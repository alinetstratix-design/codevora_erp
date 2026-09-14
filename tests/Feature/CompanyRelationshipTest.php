<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Design;
use App\Models\Material;
use App\Models\BomRule;

class CompanyRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1 — Product relationship points to Company, not CompanySetting
     */
    public function test_product_belongs_to_company()
    {
        $company = Company::factory()->create(['name' => 'Acme Corp']);
        $product = Product::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $product->company);
        $this->assertNotInstanceOf(CompanySetting::class, $product->company);
        $this->assertEquals($company->id, $product->company->id);
        $this->assertEquals('Acme Corp', $product->company->name);
    }

    /**
     * Test 2 — Customer relationship points to Company, not CompanySetting
     */
    public function test_customer_belongs_to_company()
    {
        $company = Company::factory()->create(['name' => 'Beta Industries']);
        $customer = Customer::create([
            'company_id' => $company->id,
            'customer_code' => 'CUST-001',
            'name' => 'John Doe',
        ]);

        $this->assertInstanceOf(Company::class, $customer->company);
        $this->assertNotInstanceOf(CompanySetting::class, $customer->company);
        $this->assertEquals($company->id, $customer->company->id);
        $this->assertEquals('Beta Industries', $customer->company->name);
    }

    /**
     * Test 3 — User relationship points to Company, not CompanySetting
     */
    public function test_user_belongs_to_company()
    {
        $company = Company::factory()->create(['name' => 'Gamma Systems']);
        $user = User::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $user->company);
        $this->assertNotInstanceOf(CompanySetting::class, $user->company);
        $this->assertEquals($company->id, $user->company->id);
        $this->assertEquals('Gamma Systems', $user->company->name);
    }

    /**
     * Test 4 — All other tenant models (Quotation, Design, Material, BomRule) belong to Company
     */
    public function test_all_tenant_models_belong_to_company()
    {
        $company = Company::factory()->create(['name' => 'Delta Tech']);

        $material = Material::create([
            'company_id' => $company->id,
            'name' => 'Profile Section',
            'sku' => 'PRF-01',
            'cost' => 100,
        ]);
        $this->assertInstanceOf(Company::class, $material->company);
        $this->assertEquals($company->id, $material->company->id);

        $design = Design::create([
            'company_id' => $company->id,
            'name' => 'Casement Window Design',
        ]);
        $this->assertInstanceOf(Company::class, $design->company);
        $this->assertEquals($company->id, $design->company->id);

        $product = Product::factory()->create(['company_id' => $company->id]);
        $bomRule = BomRule::create([
            'company_id' => $company->id,
            'product_id' => $product->id,
            'material_id' => $material->id,
            'rule_type' => 'Profile',
        ]);
        $this->assertInstanceOf(Company::class, $bomRule->company);
        $this->assertEquals($company->id, $bomRule->company->id);
    }

    /**
     * Test 5 — CompanySetting separation on Quotation
     * Quotation::company() resolves to Company::class (tenant identity)
     * Quotation::companySetting() resolves to CompanySetting::class (PDF branding snapshot)
     */
    public function test_company_setting_separation_on_quotation()
    {
        $company = Company::factory()->create(['name' => 'Epsilon Ltd']);
        $setting = CompanySetting::create([
            'company_id' => $company->id,
            'company_name' => 'Epsilon Ltd Branding',
            'gst_number' => 'GST12345678',
        ]);

        $quotation = Quotation::factory()->create([
            'company_id' => $company->id,
            'company_setting_id' => $setting->id,
        ]);

        // Tenant identity points to Company
        $this->assertInstanceOf(Company::class, $quotation->company);
        $this->assertNotInstanceOf(CompanySetting::class, $quotation->company);
        $this->assertEquals($company->id, $quotation->company->id);
        $this->assertEquals('Epsilon Ltd', $quotation->company->name);

        // Document branding snapshot points to CompanySetting
        $this->assertInstanceOf(CompanySetting::class, $quotation->companySetting);
        $this->assertNotInstanceOf(Company::class, $quotation->companySetting);
        $this->assertEquals($setting->id, $quotation->companySetting->id);
        $this->assertEquals('Epsilon Ltd Branding', $quotation->companySetting->company_name);
    }

    /**
     * Test 6 — Inverse relationships on Company model
     */
    public function test_company_has_inverse_relationships()
    {
        $company = Company::factory()->create(['name' => 'Zeta Corp']);
        $user = User::factory()->create(['company_id' => $company->id]);
        $product = Product::factory()->create(['company_id' => $company->id]);
        $setting = CompanySetting::create([
            'company_id' => $company->id,
            'company_name' => 'Zeta Corp Settings',
        ]);

        // Tenant scope requires authenticated user context to query tenant records
        $this->actingAs($user);

        $this->assertTrue($company->users->contains($user));
        $this->assertTrue($company->products->contains($product));
        $this->assertInstanceOf(CompanySetting::class, $company->settings);
        $this->assertEquals($setting->id, $company->settings->id);
    }

    /**
     * Test 7 — Tenant isolation regression under CompanyScope
     */
    public function test_tenant_isolation_regression()
    {
        $companyA = Company::factory()->create(['name' => 'Company A']);
        $companyB = Company::factory()->create(['name' => 'Company B']);

        $userA = User::factory()->create(['company_id' => $companyA->id]);
        $productA = Product::factory()->create(['company_id' => $companyA->id, 'name' => 'Product A']);
        $productB = Product::factory()->create(['company_id' => $companyB->id, 'name' => 'Product B']);

        // Authenticated as User A: cannot see Product B
        $this->actingAs($userA);
        $visibleProducts = Product::all();

        $this->assertTrue($visibleProducts->contains($productA));
        $this->assertFalse($visibleProducts->contains($productB));

        // Resolved relationship belongs to Company A
        $this->assertEquals($companyA->id, $productA->company->id);
    }
}
