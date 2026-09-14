<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\CompanySetting;
use App\Services\QuotationCalculationService;
use App\Services\QuotationReportBuilder;
use App\DTOs\QuotationReportDTO;

class ReportBuilderTest extends TestCase
{
    public function test_quotation_report_builder_creates_complete_dto()
    {
        $calculator = app(QuotationCalculationService::class);
        $builder = new QuotationReportBuilder($calculator);

        $quotation = new Quotation([
            'quotation_number' => 'QT-2026-0001',
            'client_name' => 'Apex Infra Projects',
            'project_name' => 'Commercial Tower A',
            'project_location' => 'Sector 62, Noida',
            'quotation_date' => '2026-06-25',
            'valid_until' => '2026-07-25',
            'no_of_components' => 54,
            'total_area_sqft' => 1051.24,
            'basic_value' => 563101.39,
            'grand_total' => 664459.64,
            'subtotal' => 563101.39,
            'gst' => 101358.25,
            'amount_in_words' => 'Six Lakh Sixty Four Thousand Four Hundred Fifty Nine Rupees and Sixty Four Paise Only'
        ]);

        $report = $builder->build($quotation);

        $this->assertInstanceOf(QuotationReportDTO::class, $report);
        $this->assertEquals('QT-2026-0001', $report->header->quoteNo);
        $this->assertEquals('Apex Infra Projects', $report->customer->name);
        $this->assertEquals(54, $report->summary->noOfComponents);
        $this->assertEquals('1051.24 Sq.Ft.', $report->summary->formattedTotalAreaSqft);
        $this->assertEquals('563,101.39 INR', $report->financials->formattedSubtotal);
        $this->assertEquals('664,459.64 INR', $report->financials->formattedGrandTotal);
    }
}
