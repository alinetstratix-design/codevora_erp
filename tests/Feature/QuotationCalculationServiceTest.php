<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Material;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Services\QuotationCalculationService;
use App\Services\BOM\BomEngine;
use App\Services\BOM\CostingEngine;

class QuotationCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected QuotationCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = new QuotationCalculationService(
            new BomEngine(),
            new CostingEngine()
        );

        CompanySetting::create([
            'company_name' => 'Test Company',
        ]);
        
        $company = Company::create(['name' => 'Test', 'database' => 'test']);
        session(['company_id' => $company->id]);
    }

    public function test_calculation_pipeline_computes_bom_cost_and_margin()
    {
        $companyId = session('company_id');
        // 1. Setup Data
        $material = Material::create([
            'company_id' => $companyId,
            'sku' => 'MAT-1',
            'name' => 'Test Profile',
            'category' => 'Profile',
            'uom' => 'kg',
            'cost' => 150.00, // 150 per kg
            'waste_percent' => 10, // 10% wastage
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $companyId,
            'product_code' => 'WIN-TEST-01',
            'name' => 'Test Window',
            'profit_margin_percent' => 20, // 20% margin/markup
            'labor_rate_per_sqft' => 50.00,
        ]);

        $product->components()->create([
            'company_id' => $companyId,
            'material_id' => $material->id,
            'component_role' => 'Outer Frame',
            'rule_type' => 'FIXED_MULTIPLIER',
            'rule_definition' => [
                'type' => 'multiplier',
                'value' => 2, // 2 units of material per item
            ],
            'unit' => 'kg',
            'sort_order' => 1,
        ]);

        // 2. Mock input payload
        $payload = [
            'discount' => 100,
            'transportation' => 500,
            'installation' => 200,
            'gst_percent' => 18,
            'items' => [
                [
                    'product_id' => $product->id,
                    'width' => 1000,
                    'height' => 1000,
                    'unit' => 'mm', // 1000x1000 mm = ~10.764 sqft
                    'qty' => 1,
                ]
            ]
        ];

        // 3. Execute
        $result = $this->service->calculateQuotationData($payload);

        // 4. Assert Area
        $item = $result['items'][0];
        $this->assertEquals(10.764, $item['area'], 'Area calculation incorrect', 0.01);

        // 5. Assert BOM Cost
        // Rule = 2 kg. Wastage = 10% -> 2.2 kg. 2.2 kg * 150/kg = 330.
        $this->assertEquals(330.00, $item['bom_cost'], 'BOM cost incorrect', 0.01);

        // 6. Assert Additional Cost
        // 10.764 sqft * 50 = 538.2
        $this->assertEquals(538.2, $item['additional_cost'], 'Additional cost incorrect', 0.1);

        // 7. Assert Cost Basis & Margin
        $expectedCostBasis = 330.00 + 538.2;
        $this->assertEquals($expectedCostBasis, $item['cost_basis'], 'Cost basis incorrect', 0.1);

        // Margin Amount (Using the markup logic currently in service) = Cost Basis * 20%
        $expectedMargin = round($expectedCostBasis * 0.20, 2);
        $this->assertEquals($expectedMargin, $item['margin_amount'], 'Margin amount incorrect');

        // Line Total
        $expectedLineTotal = round($expectedCostBasis + $expectedMargin, 2);
        $this->assertEquals($expectedLineTotal, $item['line_total'], 'Line total incorrect');

        // 8. Assert Quote Totals
        $quote = $result['quotation_data'];
        
        $this->assertEquals($expectedLineTotal, $quote['subtotal'], 'Quote subtotal incorrect', 0.1);

        $expectedTaxable = $expectedLineTotal - 100 + 500 + 200; // - discount + transport + install
        $this->assertEquals($expectedTaxable, $quote['taxable_amount'], 'Taxable amount incorrect', 0.1);

        $expectedTax = round($expectedTaxable * 0.18, 2);
        $this->assertEquals($expectedTax, $quote['tax_amount'], 'Tax amount incorrect');

        $expectedGrandTotal = round($expectedTaxable + $expectedTax, 2);
        $this->assertEquals($expectedGrandTotal, $quote['grand_total'], 'Grand total incorrect');
    }
}
