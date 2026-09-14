<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Design;
use App\Models\Quotation;
use Illuminate\Support\Facades\Cache;

class IdempotencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_submission_with_same_idempotency_key_is_ignored()
    {
        $company = \App\Models\Company::factory()->create();
        
        $setting = CompanySetting::create([
            'company_id' => $company->id,
            'company_name' => 'Test Company'
        ]);

        $user = User::factory()->create([
            'company_id' => $company->id
        ]);
        
        $product = Product::factory()->create([
            'company_id' => $company->id
        ]);
        
        $design = Design::create([
            'company_id' => $company->id,
            'name' => 'Test Design',
            'code' => 'TD-01',
            'is_active' => 1
        ]);
        $product->designs()->attach($design->id);

        $payload = [
            'project_name' => 'Idempotency Test',
            'client_name' => 'John Doe',
            'date' => '2026-08-30',
            'quote_no' => 'Q-1001',
            'idempotency_key' => 'idemp_test_9999',
            'items' => [
                [
                    'position' => 'W1',
                    'product_id' => $product->id,
                    'design_id' => $design->id,
                    'qty' => 1,
                    'dimension_w' => 1000,
                    'dimension_h' => 1200,
                ]
            ]
        ];

        // First Submission
        $response1 = $this->actingAs($user)->postJson('/quotations', $payload);
        $response1->assertStatus(200);
        $this->assertArrayHasKey('id', $response1->json());
        $quoteId = $response1->json('id');
        
        $this->assertEquals(1, Quotation::count());

        // Second Submission (Duplicate Network Retry)
        $response2 = $this->actingAs($user)->postJson('/quotations', $payload);
        $response2->assertStatus(200);
        
        // Should return the exactly same ID
        $this->assertEquals($quoteId, $response2->json('id'));
        
        // Database count should still be exactly 1
        $this->assertEquals(1, Quotation::count());
    }
}
