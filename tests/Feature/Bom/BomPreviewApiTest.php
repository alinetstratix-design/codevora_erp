<?php

namespace Tests\Feature\Bom;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Product;
use App\Models\Material;
use App\Models\BomRule;

class BomPreviewApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_bom_preview_returns_correct_calculations()
    {
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['company_id' => $company->id, 'name' => 'Window']);
        
        $material = Material::factory()->create([
            'company_id' => $company->id,
            'name' => 'Frame Profile',
            'uom' => 'm',
            'waste_percent' => 10,
            'cost' => 100
        ]);

        BomRule::factory()->create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'material_id' => $material->id,
            'component_role' => 'Frame',
            'unit' => 'mm',
            'rule_definition' => [
                'operation' => 'PERIMETER',
                'inputs' => ['width', 'height'],
                'parameters' => ['width_multiplier' => 2, 'height_multiplier' => 2]
            ]
        ]);

        $response = $this->actingAs($user)->postJson("/api/products/{$product->id}/bom-preview", [
            'width' => 1000,
            'height' => 1500,
            'quantity' => 1
        ]);

        $response->assertStatus(200);
        $data = $response->json();
        
        $this->assertCount(1, $data['bom']);
        
        $bomLine = $data['bom'][0];
        $this->assertEquals(5000, $bomLine['theoretical_qty']); // 1000*2 + 1500*2 = 5000 mm
        $this->assertEquals(5, $bomLine['normalized_qty']); // 5000 mm = 5 m
        $this->assertEquals(5.5, $bomLine['total_qty']); // 5m + 10% waste = 5.5

        $this->assertArrayHasKey('cost', $data);
        $costLine = $data['cost']['items'][0];
        $this->assertEquals(550, $costLine['total_cost']); // 5.5 * 100
        $this->assertEquals(550, $data['cost']['material_total']);
    }
}
