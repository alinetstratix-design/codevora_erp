<?php

namespace Tests\Feature;

use App\Constants\QuotationStatus;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Material;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Regression tests for the go-live bugs (tenancy, workflow, roles, auth).
 */
class SecurityRegressionTest extends TestCase
{
    use RefreshDatabase;

    private function companyWithAdmin(string $name, string $role = 'Admin'): array
    {
        $company = Company::create(['name' => $name, 'is_active' => true]);
        $user = User::factory()->create(['company_id' => $company->id, 'role' => $role, 'status' => 'Active']);
        return [$company, $user];
    }

    private function product(Company $company, string $name, string $code): Product
    {
        return Product::create([
            'company_id' => $company->id,
            'product_code' => $code,
            'name' => $name,
            'status' => 'Active',
            'base_rate' => 100,
        ]);
    }

    public function test_inactive_user_cannot_log_in(): void
    {
        [$company, $user] = $this->companyWithAdmin('A');
        $user->update(['status' => 'Inactive']);

        $response = $this->post('/login', ['email' => $user->email, 'password' => 'password']);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_product_search_never_returns_other_companies_products(): void
    {
        [$companyA, $adminA] = $this->companyWithAdmin('A');
        [$companyB] = $this->companyWithAdmin('B');

        $this->product($companyA, 'Shared Sliding Window', 'PRD-AAA111');
        $this->product($companyB, 'Shared Sliding Window', 'PRD-BBB222');

        $response = $this->actingAs($adminA)->get('/products?search=Shared');

        $response->assertOk();
        $response->assertSee('PRD-AAA111');
        $response->assertDontSee('PRD-BBB222');
    }

    public function test_staff_cannot_manage_products_or_company_settings(): void
    {
        [$company, $staff] = $this->companyWithAdmin('A', 'Staff');

        $this->actingAs($staff)->get('/products')->assertForbidden();
        $this->actingAs($staff)->get('/company-settings')->assertForbidden();
        $this->actingAs($staff)->get('/quotations')->assertOk();
    }

    public function test_customer_is_saved_with_the_users_company(): void
    {
        [$company, $admin] = $this->companyWithAdmin('A');

        $this->actingAs($admin)->post('/customers', [
            'name' => 'Ramesh Builders',
            'phone' => '9876543210',
            'status' => 'Active',
        ])->assertRedirect(route('customers.index'));

        $this->assertDatabaseHas('customers', ['name' => 'Ramesh Builders', 'company_id' => $company->id]);
        $this->assertCount(1, Customer::all(), 'Customer must be visible to its own company');
    }

    public function test_customer_phone_is_unique_only_within_a_company(): void
    {
        [$companyA, $adminA] = $this->companyWithAdmin('A');
        [$companyB, $adminB] = $this->companyWithAdmin('B');

        $this->actingAs($adminA)->post('/customers', ['name' => 'X', 'phone' => '9000000001', 'status' => 'Active']);
        $this->actingAs($adminB)->post('/customers', ['name' => 'Y', 'phone' => '9000000001', 'status' => 'Active'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('customers', 2);
    }

    public function test_approved_quotation_cannot_be_edited(): void
    {
        [$company, $admin] = $this->companyWithAdmin('A');
        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'status' => QuotationStatus::APPROVED,
        ]);

        $this->actingAs($admin)->get(route('quotations.edit', $quotation))
            ->assertRedirect(route('quotations.show', $quotation));
    }

    public function test_calculate_endpoint_does_not_leak_other_company_costing(): void
    {
        [$companyA, $adminA] = $this->companyWithAdmin('A');
        [$companyB] = $this->companyWithAdmin('B');

        $secretProduct = $this->product($companyB, 'Secret Product', 'PRD-SECRET');
        Material::withoutGlobalScopes()->create([
            'company_id' => $companyB->id,
            'sku' => 'SECRET-MAT',
            'name' => 'SecretMaterial',
            'category' => 'Profile',
            'uom' => 'Mtr',
            'cost' => 9999,
            'is_active' => true,
        ]);

        $response = $this->actingAs($adminA)->postJson(route('quotations.calculate'), [
            'items' => [[
                'product_id' => $secretProduct->id,
                'width' => 1000,
                'height' => 1000,
                'unit' => 'mm',
                'qty' => 1,
                'sizes' => [['width' => 1000, 'height' => 1000, 'unit' => 'mm', 'qty' => 1]],
            ]],
        ]);

        $response->assertOk();
        $response->assertJsonMissing(['name' => 'SecretMaterial']);
        $response->assertJsonMissing(['material_name' => 'SecretMaterial']);
        $response->assertJsonMissing(['base_rate' => 100]);
    }

    public function test_company_settings_are_isolated_per_company(): void
    {
        [$companyA, $adminA] = $this->companyWithAdmin('A');
        [$companyB] = $this->companyWithAdmin('B');
        CompanySetting::create(['company_id' => $companyB->id, 'company_name' => 'Company B Ltd']);

        $this->actingAs($adminA)->put('/company-settings', [
            'company_name' => 'Company A Ltd',
            'bank_details' => "HDFC Bank\nA/C 1234\n\nIFSC HDFC0001",
            'terms_conditions' => "Payment 50% advance\n30 days validity",
            'default_tax_percent' => 12,
        ])->assertRedirect(route('company.edit'));

        $settingA = CompanySetting::where('company_id', $companyA->id)->first();
        $settingB = CompanySetting::where('company_id', $companyB->id)->first();

        $this->assertSame('Company A Ltd', $settingA->company_name);
        $this->assertSame(['HDFC Bank', 'A/C 1234', 'IFSC HDFC0001'], $settingA->bank_details);
        $this->assertSame(['Payment 50% advance', '30 days validity'], $settingA->terms_conditions);
        $this->assertSame('Company B Ltd', $settingB->company_name);
        $this->assertNull($settingB->bank_details);
    }

    public function test_api_user_management_is_limited_to_own_company(): void
    {
        [$companyA, $adminA] = $this->companyWithAdmin('A');
        [$companyB, $adminB] = $this->companyWithAdmin('B');

        Sanctum::actingAs($adminA);

        $this->getJson('/api/users')->assertOk()
            ->assertJsonFragment(['email' => $adminA->email])
            ->assertJsonMissing(['email' => $adminB->email]);

        $this->putJson('/api/users/' . $adminB->id, ['role' => 'Staff'])->assertNotFound();
        $this->assertSame('Admin', $adminB->fresh()->role);
    }

    public function test_quotation_numbers_are_server_generated_and_sequential_per_company(): void
    {
        [$companyA] = $this->companyWithAdmin('A');
        [$companyB] = $this->companyWithAdmin('B');

        $service = app(QuotationService::class);
        $first = $service->nextQuotationNumber($companyA->id);

        Quotation::factory()->create(['company_id' => $companyA->id, 'quotation_number' => $first]);

        $second = $service->nextQuotationNumber($companyA->id);
        $this->assertNotSame($first, $second);
        $this->assertStringEndsWith('-0002', $second);
        $this->assertStringEndsWith('-0001', $service->nextQuotationNumber($companyB->id));
    }
}
