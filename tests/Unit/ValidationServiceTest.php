<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Customer;
use App\Models\CompanySetting;
use App\Services\QuotationCalculationService;
use App\Services\QuotationReportBuilder;
use App\Services\QuotationValidationService;

class ValidationServiceTest extends TestCase
{
    public function test_quotation_validation_service_evaluates_rules_and_score()
    {
        $calculator = app(QuotationCalculationService::class);
        $builder = new QuotationReportBuilder($calculator);
        $validationService = new QuotationValidationService($builder);

        $customer = new Customer([
            'name' => 'Apex Infra Projects',
            'phone' => '9599543500',
            'email' => 'contact@apexinfra.com',
            'address' => 'Sector 62, Noida'
        ]);

        $companySetting = new CompanySetting([
            'company_name' => 'SHAH ENTERPRISES',
            'gst_number' => '07AAAAA0000A1Z5',
            'address' => 'Industrial Area, New Delhi',
            'phone' => '+91 9599543500',
            'email' => 'shahenterprises044@gmail.com',
            'bank_details' => '50% Advance with Order',
            'terms_conditions' => [
                ['term' => '10 years warranty on profiles'],
                ['term' => 'Variation order clause applicable'],
                ['term' => 'Quality assurance strictly maintained'],
            ],
            'installation_prerequisites' => [
                'Site readiness required'
            ]
        ]);

        $quotation = new Quotation([
            'quotation_number' => 'SCL-QT-00001831',
            'client_name' => 'AMBALA AIRFORCE',
            'project_name' => 'AMBALA AIRFORCE',
            'project_location' => 'AMBALA AIRFORCE',
            'sales_person' => 'Authorized Signatory',
            'quotation_date' => '2026-06-25',
            'valid_until' => '2026-07-25',
            'no_of_components' => 1,
            'total_area_sqft' => 19.47,
            'basic_value' => 11829.40,
            'grand_total' => 13958.69,
            'amount_in_words' => 'Thirteen Thousand Nine Hundred Fifty Eight Rupees Only',
            'pdf_path' => 'storage/quotations/test.pdf'
        ]);

        $quotation->setRelation('customer', $customer);
        $quotation->setRelation('companySetting', $companySetting);

        $item1 = new QuotationItem([
            'item_code' => 'W1',
            'position' => 'W1',
            'profile_brand' => 'CORA',
            'profile_system' => 'CORA - 60MM CASEMENT SERIES',
            'glass_type' => '5mm Clear Toughened',
            'hardware_brand' => 'CORA Hardware',
            'width' => 878.00,
            'height' => 2060.00,
            'unit' => 'mm',
            'area' => 19.472,
            'unit_price' => 11829.40,
            'qty' => 1,
            'amount' => 11829.40,
        ]);

        $quotation->setRelation('items', collect([$item1]));

        $result = $validationService->validate($quotation);

        $this->assertNotNull($result);
        $this->assertEquals(100, $result->scorePercent);
        $this->assertTrue($result->canApprove);
        $this->assertEquals('success', $result->badgeColor);
        $this->assertIsArray($result->groupScores);
        $this->assertNotEmpty($result->allItems);
    }
}
