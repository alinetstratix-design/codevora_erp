<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Design;
use App\Models\Quotation;
use App\Models\QuotationItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuotationEditTest extends TestCase
{
    public function test_edit_quotation_screen_renders_successfully_with_complete_details()
    {
        $company = Company::firstOrCreate(['id' => 1], [
            'name' => 'Test Company',
            'email' => 'test@test.com'
        ]);

        $user = User::firstOrCreate(['email' => 'test_edit@example.com'], [
            'name' => 'Test User',
            'company_id' => $company->id,
            'password' => bcrypt('password')
        ]);
        $user->company_id = $company->id;
        $user->save();

        $this->actingAs($user);

        $suffix = uniqid();
        $customer = Customer::create([
            'company_id' => $company->id,
            'customer_code' => 'CUST-' . $suffix,
            'name' => 'Test Customer',
            'status' => 'Active'
        ]);

        $product = Product::create([
            'company_id' => $company->id,
            'product_code' => 'PRD-' . $suffix,
            'name' => 'Test Product Casement',
            'status' => 'Active',
            'glass_type' => '5mm Clear',
            'mesh_type' => 'Fiber Mesh'
        ]);

        $design = Design::create([
            'company_id' => $company->id,
            'name' => 'Fixed Window Design',
            'code' => 'FW-' . $suffix,
            'is_active' => true
        ]);
        $product->designs()->attach($design->id);

        $quotation = Quotation::create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
            'quotation_number' => 'QT-' . $suffix,
            'quotation_date' => '2026-09-14',
            'project_name' => 'Sample Villa',
            'client_name' => 'John Doe',
            'status' => 'Draft'
        ]);

        $item = $quotation->items()->create([
            'product_id' => $product->id,
            'design_id' => $design->id,
            'product_name' => 'Test Product Casement',
            'system_name' => 'Casement Window',
            'position' => 'Item 1',
            'width' => 48,
            'height' => 60,
            'area' => 20,
            'unit' => 'inch',
            'quantity' => 2,
            'amount' => 500,
            'rate' => 250,
            'handle_type' => 'Standard',
            'glass_type' => '6mm Toughened',
            'mesh_type' => 'SS Mesh',
            'profile_color' => 'Dark Grey',
            'hardware_color' => 'Black'
        ]);

        $item->sizes()->create([
            'width' => 48,
            'height' => 60,
            'unit' => 'inch',
            'quantity' => 2
        ]);

        $response = $this->actingAs($user)->get(route('quotations.edit', $quotation->id));

        $response->assertStatus(200);
        $response->assertSee('Sample Villa');
        $response->assertSee('John Doe');
        $response->assertSee('2026-09-14');
        $response->assertSee('Dark Grey');
        $response->assertSee('6mm Toughened');
        $response->assertSee('SS Mesh');
        $response->assertSee('inch');

        // Test update via PUT
        $updatePayload = [
            'customer_id' => $customer->id,
            'project_name' => 'Sample Villa Updated',
            'client_name' => 'John Doe',
            'date' => '2026-09-15',
            'quote_no' => $quotation->quotation_number,
            'status' => 'Draft',
            'items' => [
                [
                    'id' => $item->id,
                    'position' => 'Item 1',
                    'product_id' => $product->id,
                    'product_name' => 'Test Product Casement',
                    'design_id' => $design->id,
                    'design_name' => 'Fixed Window Design',
                    'qty' => 2,
                    'dimension_w' => 48,
                    'dimension_h' => 60,
                    'unit' => 'inch',
                    'sizes' => [
                        [
                            'width' => 48,
                            'height' => 60,
                            'unit' => 'inch',
                            'qty' => 2
                        ]
                    ],
                    'glass_type' => '6mm Toughened',
                    'mesh_type' => 'SS Mesh',
                    'profile_color' => 'Dark Grey',
                    'hardware_color' => 'Black'
                ]
            ]
        ];

        $updateResponse = $this->actingAs($user)->putJson(route('quotations.update', $quotation->id), $updatePayload);
        $updateResponse->assertStatus(200);
        $updateResponse->assertJson(['id' => $quotation->id]);
    }
}
