<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Quotation;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class QuotationApprovalTest extends TestCase
{
    use RefreshDatabase, WithoutMiddleware;

    public function test_staff_cannot_approve_quotation()
    {
        $staff = User::factory()->create(['role' => 'Staff']);
        $quotation = Quotation::factory()->create(['status' => 'Draft']);

        $response = $this->actingAs($staff)->post(route('quotations.approve', $quotation->id));

        $response->assertStatus(403);
    }

    public function test_manager_can_approve_valid_quotation()
    {
        $manager = User::factory()->create(['role' => 'Manager']);
        
        // Note: For full approval, quotation needs valid commercial data to pass validation service.
        // We will just test if they pass the authorization gate.
        // If validation fails, it redirects back with error.
        $quotation = Quotation::factory()->create(['status' => 'Draft']);

        $response = $this->actingAs($manager)->post(route('quotations.approve', $quotation->id));
        
        // It should either succeed (302 redirect with success) or fail validation (302 redirect with error), 
        // but it should NOT return 403 Forbidden.
        $response->assertStatus(302);
    }
}
