<?php

namespace Tests\Feature;

use App\Models\BomRule;
use App\Models\Company;
use App\Models\Design;
use App\Models\Material;
use App\Models\Product;
use App\Models\User;
use App\Services\BOM\BomEngine;
use App\Services\QuotationCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regression tests for costing correctness fixes.
 */
class CostingFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_level_overrides_are_kept_in_calculation_output(): void
    {
        $company = Company::create(['name' => 'Override Co', 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'Admin', 'status' => 'Active']);
        $product = Product::create([
            'company_id' => $company->id,
            'product_code' => 'P-OVR-1',
            'name' => 'Sliding Window',
            'base_rate' => 0,
            'status' => 'Active',
        ]);
        $design = Design::create(['company_id' => $company->id, 'name' => 'Sliding', 'code' => 'SL-1', 'is_active' => true]);

        $this->actingAs($user);

        $result = app(QuotationCalculationService::class)->calculateQuotationData([
            'items' => [[
                'product_id' => $product->id,
                'design_id' => $design->id,
                'position' => 'W1',
                'profile_brand' => 'Fenesta',
                'profile_series' => 'Series 60',
                'glass_thickness' => '5mm',
                'hardware_brand' => 'Hettich',
                'sizes' => [['width' => 1200, 'height' => 1000, 'unit' => 'mm', 'qty' => 1]],
            ]],
        ]);

        $item = $result['items'][0];
        $this->assertSame('Fenesta', $item['profile_brand']);
        $this->assertSame('Series 60', $item['profile_series']);
        $this->assertSame('5mm', $item['glass_thickness']);
        $this->assertSame('Hettich', $item['hardware_brand']);
    }

    public function test_inactive_material_on_a_required_component_is_reported_as_error(): void
    {
        $company = Company::create(['name' => 'BOM Co', 'is_active' => true]);
        $product = Product::create([
            'company_id' => $company->id,
            'product_code' => 'P-BOM-1',
            'name' => 'Fixed Window',
            'base_rate' => 0,
            'status' => 'Active',
        ]);
        $material = Material::create([
            'company_id' => $company->id,
            'sku' => 'PRF-INACTIVE',
            'name' => 'Retired Profile',
            'category' => 'Profile',
            'uom' => 'Mtr',
            'cost' => 100,
            'waste_percent' => 0,
            'is_active' => false,
        ]);
        BomRule::create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'material_id' => $material->id,
            'component_role' => 'Frame',
            'rule_type' => 'Formula',
            'rule_definition' => ['operation' => 'PERIMETER', 'inputs' => ['width', 'height']],
            'unit' => 'mm',
            'sort_order' => 1,
        ]);

        $result = app(BomEngine::class)->calculate(
            $product->fresh('components'),
            ['width' => 1000, 'height' => 1000, 'quantity' => 1, 'unit' => 'mm']
        );

        $this->assertEmpty($result['bom']);
        $this->assertNotEmpty($result['errors']);
        $this->assertStringContainsString('inactive', implode(' ', $result['errors']));
    }
}
