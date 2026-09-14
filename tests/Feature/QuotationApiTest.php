<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Quotation;

use App\Models\Product;

class QuotationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotation_api_saves_nested_sizes_correctly()
    {
        // Mock company
        $company = Company::factory()->create();
        
        $setting = CompanySetting::create([
            'company_name' => 'Test Company'
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id
        ]);
        
        $customer = Customer::create([
            'company_id' => $company->id,
            'customer_code' => 'CUST-001',
            'name' => 'John Doe',
            'phone' => '1234567890'
        ]);

        $product = Product::factory()->create([
            'company_id' => $company->id
        ]);
        
        $design = \App\Models\Design::create([
            'company_id' => $company->id,
            'name' => 'Test Design',
            'code' => 'TD-01',
            'is_active' => 1
        ]);
        $product->designs()->attach($design->id);

        $payload = [
            'customer_id' => $customer->id,
            'quote_no' => 'QT-001',
            'date' => '2026-08-01',
            'project_name' => 'API Test Project',
            'client_name' => 'API Test Client',
            'items' => [
                [
                    'system_name' => 'Sliding Window',
                    'product_id' => $product->id,
                    'design_id' => $design->id,
                    'position' => 'W1',
                    'dimension_w' => 1200,
                    'dimension_h' => 1200,
                    'qty' => 1,
                    'sizes' => [
                        ['width' => 1200, 'height' => 1200, 'qty' => 1],
                        ['width' => 1500, 'height' => 1500, 'qty' => 2]
                    ]
                ]
            ]
        ];

        // Ensure user is authenticated if auth is required, but let's assume it might not be for API or we can actAs
        $response = $this->actingAs($user, 'sanctum')->postJson('/api/quotations', $payload);

        $response->assertStatus(201);
        
        $quotation = Quotation::with('items.sizes')->first();
        
        $this->assertNotNull($quotation);
        $this->assertEquals(1, $quotation->items->count());
        $this->assertEquals(2, $quotation->items->first()->sizes->count());
        $this->assertEquals(1200, $quotation->items->first()->sizes[0]->width);
    }
}
