<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Quotation;

class TenancyIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_other_company_quotations()
    {
        $company1 = Company::factory()->create(['name' => 'Company A']);
        $company2 = Company::factory()->create(['name' => 'Company B']);
        
        $setting1 = CompanySetting::factory()->create(['company_name' => 'Company A']);
        $setting2 = CompanySetting::factory()->create(['company_name' => 'Company B']);

        $user1 = User::factory()->create(['company_id' => $company1->id]);
        
        $quotation1 = Quotation::factory()->create(['company_id' => $company1->id, 'company_setting_id' => $setting1->id]);
        $quotation2 = Quotation::factory()->create(['company_id' => $company2->id, 'company_setting_id' => $setting2->id]);

        $this->actingAs($user1);

        // Global scope should hide quotation2
        $this->assertNotNull(Quotation::find($quotation1->id));
        $this->assertNull(Quotation::find($quotation2->id));
    }

    public function test_unauthenticated_user_cannot_access_protected_quotation_routes()
    {
        // Unauthenticated browser request redirects to login route
        $responseIndex = $this->get('/quotations');
        $responseIndex->assertRedirect(route('login'));

        $responseCreate = $this->get('/quotations/create');
        $responseCreate->assertRedirect(route('login'));

        // Unauthenticated JSON request returns 401
        $responseJson = $this->getJson('/quotations');
        $responseJson->assertStatus(401);
    }

    public function test_tenant_scoped_model_fails_closed_without_authenticated_user()
    {
        $company = Company::factory()->create();
        $setting = CompanySetting::factory()->create();
        $quotation = Quotation::factory()->create(['company_id' => $company->id, 'company_setting_id' => $setting->id]);

        // Ensure completely unauthenticated
        auth()->logout();

        // Without authenticated tenant context, CompanyScope must fail closed (return 0 rows / null)
        $this->assertNull(Quotation::find($quotation->id));
        $this->assertEquals(0, Quotation::count());
    }
}
