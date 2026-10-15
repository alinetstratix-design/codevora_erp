<?php

namespace Tests\Feature;

use App\Constants\UserRole;
use App\Models\Company;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class QuotationStatusTest extends TestCase
{
    use RefreshDatabase;

    private function user(Company $company): User
    {
        return User::create([
            'company_id' => $company->id,
            'name' => 'Staff',
            'email' => 'staff' . uniqid() . '@example.test',
            'password' => Hash::make('Strong-pass-123'),
            'role' => UserRole::STAFF,
            'status' => 'Active',
            'must_change_password' => false,
        ]);
    }

    private function quotation(Company $company, string $status): Quotation
    {
        return Quotation::factory()->create([
            'company_id' => $company->id,
            'customer_id' => null,
            'company_setting_id' => null,
            'status' => $status,
        ]);
    }

    public function test_draft_cannot_be_marked_as_sent(): void
    {
        $company = Company::create(['name' => 'Acme', 'is_active' => true]);
        $quote = $this->quotation($company, 'Draft');

        $this->actingAs($this->user($company))
            ->post('/quotations/' . $quote->id . '/status', ['status' => 'Sent'])
            ->assertSessionHas('error');

        $this->assertSame('Draft', $quote->fresh()->status);
    }

    public function test_approved_then_sent_then_accepted(): void
    {
        $company = Company::create(['name' => 'Acme', 'is_active' => true]);
        $quote = $this->quotation($company, 'Approved');
        $user = $this->user($company);

        $this->actingAs($user)->post('/quotations/' . $quote->id . '/status', ['status' => 'Sent'])
            ->assertSessionHas('success');
        $this->assertSame('Sent', $quote->fresh()->status);

        $this->actingAs($user)->post('/quotations/' . $quote->id . '/status', ['status' => 'Accepted'])
            ->assertSessionHas('success');
        $this->assertSame('Accepted', $quote->fresh()->status);
    }

    public function test_accepted_quotation_cannot_be_rejected_afterwards(): void
    {
        $company = Company::create(['name' => 'Acme', 'is_active' => true]);
        $quote = $this->quotation($company, 'Accepted');

        $this->actingAs($this->user($company))
            ->post('/quotations/' . $quote->id . '/status', ['status' => 'Rejected'])
            ->assertSessionHas('error');

        $this->assertSame('Accepted', $quote->fresh()->status);
    }

    public function test_status_of_another_companys_quotation_is_not_found(): void
    {
        $acme = Company::create(['name' => 'Acme', 'is_active' => true]);
        $beta = Company::create(['name' => 'Beta', 'is_active' => true]);
        $quote = $this->quotation($beta, 'Approved');

        $this->actingAs($this->user($acme))
            ->post('/quotations/' . $quote->id . '/status', ['status' => 'Sent'])
            ->assertNotFound();
    }

    public function test_approved_quotation_cannot_be_approved_again_or_revert(): void
    {
        $company = Company::create(['name' => 'Acme', 'is_active' => true]);
        $quote = $this->quotation($company, 'Sent');

        $this->actingAs($this->user($company))
            ->post('/quotations/' . $quote->id . '/approve')
            ->assertSessionHas('error');

        $this->assertSame('Sent', $quote->fresh()->status);
    }

    public function test_list_filters_by_status_and_search(): void
    {
        $company = Company::create(['name' => 'Acme', 'is_active' => true]);
        $this->quotation($company, 'Sent');
        $this->quotation($company, 'Draft');

        $this->actingAs($this->user($company))
            ->get('/quotations?status=Sent')
            ->assertOk()
            ->assertViewHas('quotations', fn ($page) => $page->total() === 1);
    }
}
