<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Material;
use App\Models\BomRule;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Design;
use App\Services\QuotationCalculationService;
use App\Services\QuotationService;

class CalculationEngineTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $company;
    protected $product;
    protected $material;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->company = Company::create(['name' => 'Test Company']);
        CompanySetting::create(['company_id' => $this->company->id, 'company_name' => 'Test', 'terms_conditions' => '']);
        
        $this->user = User::factory()->create(['company_id' => $this->company->id, 'role' => 'Admin']);
        $this->actingAs($this->user);

        $this->product = Product::create([
            'company_id' => $this->company->id,
            'name' => 'Window Product',
            'product_code' => 'WP-1',
            'is_active' => true,
            'labor_rate_per_sqft' => 100, // 100 per sqft
            'profit_margin_percent' => 20, // 20% markup
        ]);

        $this->material = Material::create([
            'company_id' => $this->company->id,
            'name' => 'Profile',
            'sku' => 'PRF-1',
            'cost' => 50, // 50 per mm
            'uom' => 'mm',
            'is_active' => true,
        ]);

        BomRule::create([
            'company_id' => $this->company->id,
            'product_id' => $this->product->id,
            'material_id' => $this->material->id,
            'rule_type' => 'Profile',
            'rule_definition' => [
                'operation' => 'PERIMETER',
                'inputs' => ['width', 'height'],
                'parameters' => ['width_multiplier' => 2, 'height_multiplier' => 2]
            ],
        ]);
    }

    public function test_qty_one_semantics()
    {
        $service = app(QuotationCalculationService::class);
        $w = 1200;
        $h = 1500;
        
        // 1 SqFt = 92903.04 mm2
        // Area = (1200 * 1500) / 92903.04 = 19.375 SqFt
        $expectedArea = round((1200 * 1500) / 92903.04, 3);
        $expectedLabor = $expectedArea * 100; // 1937.5
        $perimeter = (1200 * 2) + (1500 * 2); // 5400
        $expectedBomCost = $perimeter * 50; // 270,000
        $costBasis = $expectedBomCost + $expectedLabor;
        $expectedMargin = $costBasis * 0.20;
        $expectedLineTotal = $costBasis + $expectedMargin;

        $data = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'width' => 1200,
                    'height' => 1500,
                    'qty' => 1,
                    'unit' => 'mm'
                ]
            ]
        ];

        $result = $service->calculateQuotationData($data);
        $item = $result['items'][0];
        
        // dd($item['bom_result']);
        if ($item['bom_cost'] == 0) {
            // empty
        }

        $this->assertEquals(1, $item['quantity']);
        $this->assertEquals($expectedArea, $item['area']);
        $this->assertEquals(round($expectedBomCost, 2), $item['bom_cost']);
        $this->assertEquals(round($expectedLabor, 2), $item['additional_cost']);
        $this->assertEquals(round($expectedLineTotal, 2), $item['line_total']);
    }

    public function test_qty_two_semantics()
    {
        $service = app(QuotationCalculationService::class);
        
        $data = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'width' => 1200,
                    'height' => 1500,
                    'qty' => 2,
                    'unit' => 'mm'
                ]
            ]
        ];

        $result = $service->calculateQuotationData($data);
        $item = $result['items'][0];

        $expectedUnitArea = round((1200 * 1500) / 92903.04, 3);
        $expectedTotalArea = $expectedUnitArea * 2;
        $expectedLabor = $expectedTotalArea * 100;
        $expectedBomCost = ((1200 * 2) + (1500 * 2)) * 50 * 2; // Quantity applies to material cost
        $costBasis = $expectedBomCost + $expectedLabor;
        $expectedMargin = $costBasis * 0.20;
        $expectedLineTotal = $costBasis + $expectedMargin;

        $this->assertEquals(2, $item['quantity']);
        $this->assertEquals(round($expectedTotalArea, 3), $item['area']);
        $this->assertEquals(round($expectedBomCost, 2), $item['bom_cost']);
        $this->assertEquals(round($expectedLabor, 2), $item['additional_cost']);
        $this->assertEquals(round($expectedLineTotal, 2), $item['line_total']);
    }

    public function test_multiple_sizes_aggregation()
    {
        $service = app(QuotationCalculationService::class);
        
        $data = [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'unit' => 'mm',
                    'sizes' => [
                        ['width' => 1200, 'height' => 1500, 'qty' => 2, 'unit' => 'mm'],
                        ['width' => 1800, 'height' => 1500, 'qty' => 4, 'unit' => 'mm'],
                        ['width' => 2400, 'height' => 1800, 'qty' => 1, 'unit' => 'mm'],
                    ]
                ]
            ]
        ];

        $result = $service->calculateQuotationData($data);
        $item = $result['items'][0];

        // Total quantity should be 7
        $this->assertEquals(7, $item['quantity']);
        $this->assertCount(3, $item['sizes']);

        $size1Area = round((1200 * 1500) / 92903.04, 3) * 2;
        $size2Area = round((1800 * 1500) / 92903.04, 3) * 4;
        $size3Area = round((2400 * 1800) / 92903.04, 3) * 1;
        $expectedTotalArea = $size1Area + $size2Area + $size3Area;
        
        $this->assertEquals(round($expectedTotalArea, 3), $item['area']);
    }

    public function test_snapshot_integrity()
    {
        $quotationService = app(QuotationService::class);
        
        $data = [
            'quotation_number' => 'TEST-123',
            'client_name' => 'Test Client',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'width' => 1200,
                    'height' => 1500,
                    'qty' => 1,
                    'unit' => 'mm'
                ]
            ]
        ];

        $quotation = $quotationService->saveDraft($data);
        $originalGrandTotal = $quotation->grand_total;

        // Mutate product price
        $this->product->update(['labor_rate_per_sqft' => 500]);
        $this->material->update(['unit_cost' => 200]);

        // Refetch quotation, should still have original total
        $quotation->refresh();
        $this->assertEquals($originalGrandTotal, $quotation->grand_total);
        
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'grand_total' => $originalGrandTotal
        ]);
    }

    public function test_tenant_isolation_on_design_api()
    {
        $company2 = Company::create(['name' => 'Company B']);
        CompanySetting::create(['company_id' => $company2->id, 'company_name' => 'Comp B']);
        
        $designA = Design::create([
            'company_id' => $this->company->id,
            'name' => 'Design A'
        ]);

        $designB = Design::create([
            'company_id' => $company2->id,
            'name' => 'Design B'
        ]);

        // User is Company A
        $response = $this->getJson('/api/designs');
        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment(['name' => 'Design A']);
        $response->assertJsonMissing(['name' => 'Design B']);
    }
}
