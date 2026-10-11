<?php

namespace Tests\Feature;

use App\Constants\QuotationStatus;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function quotationFor(Company $company): Quotation
    {
        return Quotation::factory()->create(['company_id' => $company->id, 'status' => QuotationStatus::DRAFT]);
    }

    public function test_staff_cannot_approve_quotation(): void
    {
        $company = Company::create(['name' => 'A', 'is_active' => true]);
        $staff = User::factory()->create(['company_id' => $company->id, 'role' => 'Staff']);
        $quotation = $this->quotationFor($company);

        $this->actingAs($staff)->post(route('quotations.approve', $quotation->id))->assertForbidden();
        $this->assertSame(QuotationStatus::DRAFT, $quotation->fresh()->status);
    }

    public function test_manager_approval_is_blocked_while_validation_is_incomplete(): void
    {
        $company = Company::create(['name' => 'A', 'is_active' => true]);
        $manager = User::factory()->create(['company_id' => $company->id, 'role' => 'Manager']);
        $quotation = $this->quotationFor($company);

        $response = $this->actingAs($manager)->post(route('quotations.approve', $quotation->id));

        // An incomplete quotation must not be approved; the user is told why.
        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertSame(QuotationStatus::DRAFT, $quotation->fresh()->status);
    }

    public function test_manager_from_another_company_cannot_approve(): void
    {
        $companyA = Company::create(['name' => 'A', 'is_active' => true]);
        $companyB = Company::create(['name' => 'B', 'is_active' => true]);
        $managerB = User::factory()->create(['company_id' => $companyB->id, 'role' => 'Manager']);
        $quotation = $this->quotationFor($companyA);

        $this->actingAs($managerB)->post(route('quotations.approve', $quotation->id))->assertNotFound();
    }
}
