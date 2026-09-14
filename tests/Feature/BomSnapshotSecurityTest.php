<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Product;
use App\Models\Material;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Services\QuotationService;

class BomSnapshotSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected QuotationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->service = app(QuotationService::class);
    }

    public function test_master_data_changes_do_not_alter_historical_quotations()
    {
        // 1. Setup: Master Data
        $company = Company::factory()->create();
        $setting = CompanySetting::factory()->create(['company_name' => 'Test Company']);
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        
        session(['company_id' => $company->id]);

        $material = Material::create([
            'company_id' => $company->id,
            'sku' => 'MAT-1',
            'name' => 'Test Profile',
            'category' => 'Profile',
            'uom' => 'kg',
            'cost' => 150.00,
            'waste_percent' => 10,
            'is_active' => true,
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'product_code' => 'WIN-TEST-02',
            'name' => 'Test Window',
            'profit_margin_percent' => 20,
            'labor_rate_per_sqft' => 50.00,
        ]);

        $product->components()->create([
            'company_id' => $company->id,
            'material_id' => $material->id,
            'component_role' => 'Outer Frame',
            'rule_type' => 'FIXED_MULTIPLIER',
            'rule_definition' => [
                'type' => 'multiplier',
                'value' => 2,
            ],
            'unit' => 'kg',
            'sort_order' => 1,
        ]);

        // 2. Save Quotation
        $payload = [
            'quotation_number' => 'TEST-QT-001',
            'client_name' => 'Test Client',
            'discount' => 0,
            'transportation' => 0,
            'installation' => 0,
            'gst_percent' => 0,
            'items' => [
                [
                    'product_id' => $product->id,
                    'width' => 1000,
                    'height' => 1000,
                    'unit' => 'mm', // 10.764 sqft
                    'qty' => 1,
                ]
            ]
        ];

        $quotation = $this->service->saveDraft($payload);
        $originalGrandTotal = $quotation->grand_total;

        $this->assertGreaterThan(0, $originalGrandTotal);

        $savedItem = $quotation->items->first();
        $savedBom = $savedItem->boms->first();
        
        $this->assertEquals(150.00, $savedBom->unit_cost);

        // 3. Mutate Master Data
        $material->update(['cost' => 500.00]);
        $product->update(['profit_margin_percent' => 50]);

        // 4. Assert Historical Quotation is Untouched
        $refreshedQuotation = $quotation->fresh();
        
        $this->assertEquals($originalGrandTotal, $refreshedQuotation->grand_total, 'Grand total changed after master data update');
        
        $refreshedItem = $refreshedQuotation->items->first();
        $refreshedBom = $refreshedItem->boms->first();
        
        $this->assertEquals(150.00, $refreshedBom->unit_cost, 'Historical BOM unit cost changed');
    }
}
