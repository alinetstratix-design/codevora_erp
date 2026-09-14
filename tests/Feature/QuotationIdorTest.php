<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Quotation;

class QuotationIdorTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_update_other_company_quotations_via_api()
    {
        $company1 = Company::factory()->create(['name' => 'Company A']);
        $company2 = Company::factory()->create(['name' => 'Company B']);
        
        $setting1 = CompanySetting::factory()->create(['company_name' => 'Company A']);
        $setting2 = CompanySetting::factory()->create(['company_name' => 'Company B']);

        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $quotation2 = Quotation::factory()->create(['company_id' => $company2->id, 'company_setting_id' => $setting2->id]);

        $product = \App\Models\Product::factory()->create(['company_id' => $company1->id]);
        $design = \App\Models\Design::create([
            'company_id' => $company1->id,
            'name' => 'Test Design',
            'code' => 'TD-01',
            'is_active' => 1
        ]);
        $product->designs()->attach($design->id);

        $response = $this->actingAs($user1)->putJson("/api/quotations/{$quotation2->id}", [
            'project_name' => 'Hacked Project',
            'client_name' => 'Hacked Client',
            'date' => '2026-01-01',
            'quote_no' => 'HACK-01',
            'items' => [
                [
                    'product_id' => $product->id,
                    'design_id' => $design->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000
                ]
            ]
        ]);

        // Since the Global Scope hides it, findOrFail in the controller will throw 404.
        // If they somehow bypassed the scope, the Policy would throw 403.
        $response->assertStatus(404);
    }

    public function test_valid_product_and_associated_design_passes_validation()
    {
        $company = Company::factory()->create();
        CompanySetting::create(['company_id' => $company->id, 'company_name' => 'Test Company']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $customer = \App\Models\Customer::create([
            'company_id' => $company->id,
            'customer_code' => 'CUST-001',
            'name' => 'John Doe',
        ]);

        $productA = \App\Models\Product::factory()->create(['company_id' => $company->id]);
        $design1 = \App\Models\Design::create([
            'company_id' => $company->id,
            'name' => 'Design 1',
            'code' => 'D1',
            'is_active' => 1,
        ]);
        $productA->designs()->attach($design1->id);

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer->id,
            'quote_no' => 'QT-VALID',
            'date' => '2026-08-01',
            'project_name' => 'Valid Project',
            'client_name' => 'Valid Client',
            'items' => [
                [
                    'product_id' => $productA->id,
                    'design_id' => $design1->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);

        $response->assertStatus(201);
    }

    public function test_valid_product_with_unrelated_design_fails_validation()
    {
        $company = Company::factory()->create();
        CompanySetting::create(['company_id' => $company->id, 'company_name' => 'Test Company']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $customer = \App\Models\Customer::create([
            'company_id' => $company->id,
            'customer_code' => 'CUST-001',
            'name' => 'John Doe',
        ]);

        // Product A has Design 1 and Design 2
        // Product B has Design 3
        $productA = \App\Models\Product::factory()->create(['company_id' => $company->id]);
        $productB = \App\Models\Product::factory()->create(['company_id' => $company->id]);

        $design1 = \App\Models\Design::create(['company_id' => $company->id, 'name' => 'Design 1', 'is_active' => 1]);
        $design2 = \App\Models\Design::create(['company_id' => $company->id, 'name' => 'Design 2', 'is_active' => 1]);
        $design3 = \App\Models\Design::create(['company_id' => $company->id, 'name' => 'Design 3', 'is_active' => 1]);

        $productA->designs()->attach([$design1->id, $design2->id]);
        $productB->designs()->attach([$design3->id]);

        // Case C: Product A + Design 3 -> REJECT
        $responseA = $this->actingAs($user, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer->id,
            'quote_no' => 'QT-INVALID-A',
            'date' => '2026-08-01',
            'project_name' => 'Invalid Project',
            'client_name' => 'Client',
            'items' => [
                [
                    'product_id' => $productA->id,
                    'design_id' => $design3->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);

        $responseA->assertStatus(422);
        $responseA->assertJsonValidationErrors(['items.0.design_id']);

        // Case C: Product B + Design 1 -> REJECT
        $responseB = $this->actingAs($user, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer->id,
            'quote_no' => 'QT-INVALID-B',
            'date' => '2026-08-01',
            'project_name' => 'Invalid Project',
            'client_name' => 'Client',
            'items' => [
                [
                    'product_id' => $productB->id,
                    'design_id' => $design1->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);

        $responseB->assertStatus(422);
        $responseB->assertJsonValidationErrors(['items.0.design_id']);
    }

    public function test_cross_tenant_product_design_combination_fails_validation()
    {
        $company1 = Company::factory()->create(['name' => 'Tenant 1']);
        $company2 = Company::factory()->create(['name' => 'Tenant 2']);
        CompanySetting::create(['company_id' => $company1->id, 'company_name' => 'Tenant 1']);
        CompanySetting::create(['company_id' => $company2->id, 'company_name' => 'Tenant 2']);

        $user1 = User::factory()->create(['company_id' => $company1->id]);
        $customer1 = \App\Models\Customer::create([
            'company_id' => $company1->id,
            'customer_code' => 'CUST-001',
            'name' => 'Tenant 1 Customer',
        ]);

        // Tenant 1 records
        $product1 = \App\Models\Product::factory()->create(['company_id' => $company1->id]);
        $design1 = \App\Models\Design::create(['company_id' => $company1->id, 'name' => 'Design T1', 'is_active' => 1]);
        $product1->designs()->attach($design1->id);

        // Tenant 2 records
        $product2 = \App\Models\Product::factory()->create(['company_id' => $company2->id]);
        $design2 = \App\Models\Design::create(['company_id' => $company2->id, 'name' => 'Design T2', 'is_active' => 1]);
        $product2->designs()->attach($design2->id);

        // Attempt 1: Tenant 1 user submits Tenant 1 product + Tenant 2 design -> REJECT
        $res1 = $this->actingAs($user1, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer1->id,
            'quote_no' => 'QT-XT-1',
            'date' => '2026-08-01',
            'project_name' => 'Cross Tenant 1',
            'client_name' => 'Client',
            'items' => [
                [
                    'product_id' => $product1->id,
                    'design_id' => $design2->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);
        $res1->assertStatus(422);
        $res1->assertJsonValidationErrors(['items.0.design_id']);

        // Attempt 2: Tenant 1 user submits Tenant 2 product + Tenant 1 design -> REJECT
        $res2 = $this->actingAs($user1, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer1->id,
            'quote_no' => 'QT-XT-2',
            'date' => '2026-08-01',
            'project_name' => 'Cross Tenant 2',
            'client_name' => 'Client',
            'items' => [
                [
                    'product_id' => $product2->id,
                    'design_id' => $design1->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);
        $res2->assertStatus(422);
        $res2->assertJsonValidationErrors(['items.0.design_id']);

        // Attempt 3: Tenant 1 user submits Tenant 2 product + Tenant 2 design -> REJECT
        $res3 = $this->actingAs($user1, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer1->id,
            'quote_no' => 'QT-XT-3',
            'date' => '2026-08-01',
            'project_name' => 'Cross Tenant 3',
            'client_name' => 'Client',
            'items' => [
                [
                    'product_id' => $product2->id,
                    'design_id' => $design2->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);
        $res3->assertStatus(422);
        $res3->assertJsonValidationErrors(['items.0.design_id']);
    }

    public function test_missing_product_or_design_fails_validation()
    {
        $company = Company::factory()->create();
        CompanySetting::create(['company_id' => $company->id, 'company_name' => 'Test Company']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $customer = \App\Models\Customer::create([
            'company_id' => $company->id,
            'customer_code' => 'CUST-001',
            'name' => 'John Doe',
        ]);

        $product = \App\Models\Product::factory()->create(['company_id' => $company->id]);
        $design = \App\Models\Design::create(['company_id' => $company->id, 'name' => 'Design 1', 'is_active' => 1]);
        $product->designs()->attach($design->id);

        // Case A: Design does not exist -> REJECT
        $resA = $this->actingAs($user, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer->id,
            'quote_no' => 'QT-MISSING-A',
            'date' => '2026-08-01',
            'project_name' => 'Missing Design',
            'client_name' => 'Client',
            'items' => [
                [
                    'product_id' => $product->id,
                    'design_id' => 999999,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);
        $resA->assertStatus(422);
        $resA->assertJsonValidationErrors(['items.0.design_id']);

        // Case B: Product does not exist -> REJECT
        $resB = $this->actingAs($user, 'sanctum')->postJson('/api/quotations', [
            'customer_id' => $customer->id,
            'quote_no' => 'QT-MISSING-B',
            'date' => '2026-08-01',
            'project_name' => 'Missing Product',
            'client_name' => 'Client',
            'items' => [
                [
                    'product_id' => 999999,
                    'design_id' => $design->id,
                    'position' => 'W1',
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1000,
                ]
            ]
        ]);
        $resB->assertStatus(422);
        $resB->assertJsonValidationErrors(['items.0.design_id']);
    }
}
