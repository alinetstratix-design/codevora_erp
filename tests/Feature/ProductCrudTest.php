<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Product;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $company;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->company = Company::factory()->create(['name' => 'Acme Windows']);
        CompanySetting::create([
            'company_id' => $this->company->id,
            'company_name' => 'Acme Windows'
        ]);
        $this->user = User::factory()->create([
            'company_id' => $this->company->id,
            'role' => 'Admin'
        ]);
    }

    /**
     * Test 1 — Core Product fields persist
     */
    public function test_core_product_fields_persist()
    {
        $payload = [
            'name' => 'Core Window System',
            'category' => 'Casement Window',
            'opening_type' => 'Casement Outward',
            'profile_brand' => 'CORA',
            'profile_series' => '60MM',
            'glass_type' => '5mm Clear',
            'glass_thickness' => '5mm',
            'mesh_type' => 'SS FLYMESH',
            'hardware_brand' => 'CORA Hardware',
            'unit' => 'Sq.Ft.',
            'base_rate' => 650.00,
            'description' => 'Test core product description',
            'status' => 'Active',
        ];

        $response = $this->actingAs($this->user)->post('/products', $payload);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasNoErrors();

        $product = Product::where('name', 'Core Window System')->first();
        $this->assertNotNull($product);
        $this->assertEquals('Casement Window', $product->category);
        $this->assertEquals('Casement Outward', $product->opening_type);
        $this->assertEquals('CORA', $product->profile_brand);
        $this->assertEquals('60MM', $product->profile_series);
        $this->assertEquals('5mm Clear', $product->glass_type);
        $this->assertEquals('5mm', $product->glass_thickness);
        $this->assertEquals('SS FLYMESH', $product->mesh_type);
        $this->assertEquals('CORA Hardware', $product->hardware_brand);
        $this->assertEquals('Sq.Ft.', $product->unit);
        $this->assertEquals(650.00, (float)$product->base_rate);
        $this->assertEquals('Test core product description', $product->description);
        $this->assertEquals('Active', $product->status);
        $this->assertEquals($this->company->id, $product->company_id);
    }

    /**
     * Test 2 — Advanced Product fields persist (BOM, costing, labor, margin, formula, details)
     */
    public function test_advanced_product_fields_persist()
    {
        $payload = [
            'name' => 'Advanced Casement Window',
            'category' => 'Casement Window',
            'base_rate' => 750.00,
            'profile_weight_per_mtr' => 1.450,
            'profile_rate_per_kg' => 320.00,
            'glass_rate_per_sqft' => 180.00,
            'hardware_kit_cost' => 450.00,
            'wastage_percent' => 12.50,
            'profit_margin_percent' => 25.00,
            'labor_rate_per_sqft' => 85.00,
            'profile_calc_formula' => 'PERIMETER',
            'profile_details' => [
                'Profile Color' => 'WHITE',
                'MeshType' => 'No',
                'Outer' => 'CORA Outer 60',
            ],
            'accessories_details' => [
                'Locking' => 'Multi-point',
                'Handle color' => 'WHITE',
            ],
        ];

        $response = $this->actingAs($this->user)->post('/products', $payload);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasNoErrors();

        $product = Product::where('name', 'Advanced Casement Window')->first();
        $this->assertNotNull($product);

        $this->assertEquals(1.450, (float)$product->profile_weight_per_mtr);
        $this->assertEquals(320.00, (float)$product->profile_rate_per_kg);
        $this->assertEquals(180.00, (float)$product->glass_rate_per_sqft);
        $this->assertEquals(450.00, (float)$product->hardware_kit_cost);
        $this->assertEquals(12.50, (float)$product->wastage_percent);
        $this->assertEquals(25.00, (float)$product->profit_margin_percent);
        $this->assertEquals(85.00, (float)$product->labor_rate_per_sqft);
        $this->assertEquals('PERIMETER', $product->profile_calc_formula);

        $this->assertIsArray($product->profile_details);
        $this->assertEquals('WHITE', $product->profile_details['Profile Color']);
        $this->assertEquals('CORA Outer 60', $product->profile_details['Outer']);

        $this->assertIsArray($product->accessories_details);
        $this->assertEquals('Multi-point', $product->accessories_details['Locking']);
        $this->assertEquals('WHITE', $product->accessories_details['Handle color']);
    }

    /**
     * Test 3 — Update does not silently lose fields
     */
    public function test_update_does_not_silently_lose_fields()
    {
        $product = Product::create([
            'company_id' => $this->company->id,
            'product_code' => 'PRD-TEST01',
            'name' => 'Initial Window',
            'base_rate' => 500.00,
            'profile_weight_per_mtr' => 2.100,
            'profile_rate_per_kg' => 300.00,
            'glass_rate_per_sqft' => 150.00,
            'hardware_kit_cost' => 400.00,
            'wastage_percent' => 10.00,
            'profit_margin_percent' => 20.00,
            'labor_rate_per_sqft' => 50.00,
            'profile_calc_formula' => 'PERIMETER',
            'profile_details' => ['Profile Color' => 'BLACK'],
            'accessories_details' => ['Locking' => 'Single'],
        ]);

        // Update name and labor rate, keeping other advanced fields
        $updatePayload = [
            'name' => 'Renamed Window',
            'base_rate' => 500.00,
            'profile_weight_per_mtr' => 2.100,
            'profile_rate_per_kg' => 300.00,
            'glass_rate_per_sqft' => 150.00,
            'hardware_kit_cost' => 400.00,
            'wastage_percent' => 10.00,
            'profit_margin_percent' => 20.00,
            'labor_rate_per_sqft' => 95.00, // modified
            'profile_calc_formula' => 'PERIMETER',
            'profile_details' => ['Profile Color' => 'BLACK'],
            'accessories_details' => ['Locking' => 'Single'],
        ];

        $response = $this->actingAs($this->user)->put("/products/{$product->id}", $updatePayload);

        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasNoErrors();

        $product->refresh();
        $this->assertEquals('Renamed Window', $product->name);
        $this->assertEquals(95.00, (float)$product->labor_rate_per_sqft);
        $this->assertEquals(2.100, (float)$product->profile_weight_per_mtr);
        $this->assertEquals(300.00, (float)$product->profile_rate_per_kg);
        $this->assertEquals(150.00, (float)$product->glass_rate_per_sqft);
        $this->assertEquals(400.00, (float)$product->hardware_kit_cost);
        $this->assertEquals(10.00, (float)$product->wastage_percent);
        $this->assertEquals(20.00, (float)$product->profit_margin_percent);
        $this->assertEquals('PERIMETER', $product->profile_calc_formula);
        $this->assertEquals('BLACK', $product->profile_details['Profile Color']);
        $this->assertEquals('Single', $product->accessories_details['Locking']);
    }

    /**
     * Test 4 — Invalid values are rejected
     */
    public function test_invalid_values_are_rejected()
    {
        $payload = [
            'name' => 'Invalid Product Test',
            'profile_weight_per_mtr' => -5.0, // negative not allowed
            'labor_rate_per_sqft' => -20.0, // negative not allowed
            'profile_calc_formula' => 'NON_EXISTENT_FORMULA', // invalid enum
            'status' => 'Archived', // invalid enum
            'profile_details' => 'not-an-array', // must be array
        ];

        $response = $this->actingAs($this->user)->postJson('/products', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'profile_weight_per_mtr',
            'labor_rate_per_sqft',
            'profile_calc_formula',
            'status',
            'profile_details',
        ]);
    }

    /**
     * Test 5 — Mass assignment remains protected
     */
    public function test_mass_assignment_remains_protected()
    {
        $payload = [
            'name' => 'Protected Attributes Test',
            'base_rate' => 100.00,
            'is_admin' => 1,
            'arbitrary_injected_field' => 'hacked',
        ];

        $response = $this->actingAs($this->user)->post('/products', $payload);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('name', 'Protected Attributes Test')->first();
        $this->assertNotNull($product);

        // Verify unexpected attributes are not on the model
        $this->assertFalse(isset($product->is_admin));
        $this->assertFalse(isset($product->arbitrary_injected_field));
    }

    /**
     * Test 6 — Tenant isolation on product CRUD
     */
    public function test_tenant_isolation_on_product_crud()
    {
        $company2 = Company::factory()->create(['name' => 'Beta Windows']);
        $user2 = User::factory()->create(['company_id' => $company2->id]);

        $product1 = Product::create([
            'company_id' => $this->company->id,
            'product_code' => 'PRD-T1',
            'name' => 'Tenant 1 Product',
            'base_rate' => 500.00,
        ]);

        // User 2 cannot access Product 1 edit page (fails closed with 404)
        $response = $this->actingAs($user2)->get("/products/{$product1->id}/edit");
        $response->assertStatus(404);

        // User 2 cannot update Product 1
        $updateResponse = $this->actingAs($user2)->put("/products/{$product1->id}", [
            'name' => 'Hijacked Product',
        ]);
        $updateResponse->assertStatus(404);

        // Verify Product 1 unchanged
        $product1->refresh();
        $this->assertEquals('Tenant 1 Product', $product1->name);
    }
}
