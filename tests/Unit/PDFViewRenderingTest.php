<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Services\PDFService;
use App\Services\QuotationCalculator;
use App\Services\QuotationReportBuilder;

class PDFViewRenderingTest extends TestCase
{
    public function test_pdf_service_generates_pdf_using_report_dto_and_partials()
    {
        $calculator = new QuotationCalculator();
        $builder = new QuotationReportBuilder($calculator);
        $pdfService = new PDFService($builder);

        $quotation = new Quotation([
            'quotation_number' => 'SCL-QT-00001831',
            'client_name' => 'AMBALA AIRFORCE',
            'project_name' => 'AMBALA AIRFORCE',
            'project_location' => 'AMBALA AIRFORCE',
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

        $item1 = new QuotationItem([
            'item_code' => 'W1',
            'position' => 'W1',
            'profile_system' => 'CORA - 60MM CASEMENT SERIES',
            'width' => 878.00,
            'height' => 2060.00,
            'unit' => 'mm',
            'area' => 19.472,
            'weight_kg' => 42.109,
            'unit_price' => 11829.40,
            'value_per_sqft' => 607.51,
            'qty' => 1,
            'amount' => 11829.40,
        ]);

        $quotation->setRelation('items', collect([$item1]));

        $pdfPath = $pdfService->generateQuotationPDF($quotation);

        $this->assertNotEmpty($pdfPath);
        $this->assertStringContainsString('storage/quotations/', $pdfPath);
    }
}
