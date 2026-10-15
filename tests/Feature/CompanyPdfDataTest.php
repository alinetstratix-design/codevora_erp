<?php

namespace Tests\Feature;

use App\Constants\QuotationStatus;
use App\DTOs\ItemDTO;
use App\Models\Company;
use App\Models\Quotation;
use App\Services\CompanySettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyPdfDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_item_without_company_specs_prints_dashes_not_another_companys_claims(): void
    {
        $item = new ItemDTO(['width' => 1200, 'height' => 1500]);

        $this->assertSame('—', $item->commercialSpecs['Glass Make']);
        $this->assertSame('—', $item->commercialSpecs['Warranty']);
        $this->assertSame('—', $item->commercialSpecs['Hardware Brand']);
        $this->assertSame('—', $item->location);
        $this->assertSame([], $item->inclusions);
        $this->assertStringNotContainsString('Saint-Gobain', json_encode($item->commercialSpecs));
        $this->assertStringNotContainsString('CORA', json_encode($item->commercialSpecs));
    }

    public function test_company_specs_flow_into_item_card(): void
    {
        $item = new ItemDTO([
            'width' => 1200,
            'height' => 1500,
            'company_specs' => [
                'glass_make' => 'Own Glass Co',
                'warranty' => '5 years',
                'inclusions' => ['Frame assembly'],
            ],
        ]);

        $this->assertSame('Own Glass Co', $item->commercialSpecs['Glass Make']);
        $this->assertSame('5 years', $item->commercialSpecs['Warranty']);
        $this->assertSame(['Frame assembly'], $item->inclusions);
    }

    public function test_settings_change_refreshes_only_draft_pdfs(): void
    {
        $company = Company::create(['name' => 'Acme', 'is_active' => true]);

        $draft = Quotation::factory()->create(['company_id' => $company->id, 'customer_id' => null, 'company_setting_id' => null, 'status' => QuotationStatus::DRAFT]);
        $sent = Quotation::factory()->create(['company_id' => $company->id, 'customer_id' => null, 'company_setting_id' => null, 'status' => QuotationStatus::SENT]);

        app(CompanySettingService::class)->updateSettings($company->id, ['company_name' => 'Acme Windows']);

        $this->assertTrue((bool) $draft->fresh()->pdf_stale);
        $this->assertFalse((bool) $sent->fresh()->pdf_stale, 'Sent quotations keep the terms they were sent with');
    }
}
