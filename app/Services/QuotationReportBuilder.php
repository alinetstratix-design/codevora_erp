<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\CompanySetting;
use App\Helpers\SvgGenerator;

use App\DTOs\CompanyDTO;
use App\DTOs\CustomerDTO;
use App\DTOs\ProjectDTO;
use App\DTOs\QuotationHeaderDTO;
use App\DTOs\QuotationSummaryDTO;
use App\DTOs\FinancialSummaryDTO;
use App\DTOs\ItemDTO;
use App\DTOs\DrawingDTO;
use App\DTOs\ProfileDTO;
use App\DTOs\GlassDTO;
use App\DTOs\HardwareDTO;
use App\DTOs\AccessoryDTO;
use App\DTOs\TermsDTO;
use App\DTOs\BankDTO;
use App\DTOs\SignatureDTO;
use App\DTOs\FooterDTO;
use App\DTOs\QuotationReportDTO;

class QuotationReportBuilder
{
    protected QuotationCalculator $calculator;

    public function __construct(QuotationCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Build full QuotationReportDTO from a Quotation Model.
     */
    public function build(Quotation $quotation): QuotationReportDTO
    {
        $quotation->loadMissing(['items.sizes', 'customer', 'companySetting']);

        $company = $this->buildCompany($quotation);
        $customer = $this->buildCustomer($quotation);
        $project = $this->buildProject($quotation);
        $header = $this->buildHeader($quotation);
        $summary = $this->buildSummary($quotation);
        $financials = $this->buildFinancials($quotation);
        $items = $this->buildItems($quotation);
        $terms = $this->buildTerms($quotation);
        $bank = $this->buildBank($quotation);
        $signature = $this->buildSignature($quotation);
        $footer = $this->buildFooter($quotation);
        $enclosures = $quotation->cover_letter_enclosures ?? [
            'a. Window design, specification and value',
            'b. Terms and Conditions'
        ];

        return new QuotationReportDTO(
            $company,
            $customer,
            $project,
            $header,
            $summary,
            $financials,
            $items,
            $terms,
            $bank,
            $signature,
            $footer,
            $enclosures
        );
    }

    public function buildCompany(Quotation $quotation): CompanyDTO
    {
        $setting = $quotation->companySetting ?? CompanySetting::first();
        $data = $setting ? $setting->toArray() : [];
        return new CompanyDTO($data);
    }

    public function buildCustomer(Quotation $quotation): CustomerDTO
    {
        $cust = $quotation->customer;
        $data = [
            'name' => $quotation->client_name ?? ($cust ? $cust->name : 'AMBALA AIRFORCE'),
            'company_name' => $cust ? $cust->company_name : '',
            'phone' => $cust ? $cust->phone : '',
            'email' => $cust ? $cust->email : '',
            'address' => $quotation->address ?? ($cust ? $cust->address : ''),
            'gst_number' => $cust ? $cust->gst_number : '',
        ];
        return new CustomerDTO($data);
    }

    public function buildProject(Quotation $quotation): ProjectDTO
    {
        $data = [
            'name' => $quotation->project_name ?? $quotation->client_name ?? 'AMBALA AIRFORCE',
            'location' => $quotation->project_location ?? $quotation->address ?? 'AMBALA AIRFORCE',
            'sales_person' => $quotation->sales_person ?? 'Authorized Signatory',
            'remarks' => $quotation->remarks ?? '',
        ];
        return new ProjectDTO($data);
    }

    public function buildHeader(Quotation $quotation): QuotationHeaderDTO
    {
        $data = [
            'quote_no' => $quotation->quotation_number ?? $quotation->quote_no ?? 'SCL-QT-00001831',
            'date' => $quotation->quotation_date ?? $quotation->date ?? date('Y-m-d'),
            'valid_till' => $quotation->valid_until ?? $quotation->valid_till ?? date('Y-m-d', strtotime('+30 days')),
            'status' => $quotation->status ?? 'Draft',
            'opportunity_no' => $quotation->opportunity_no ?? '',
            'project_name' => $quotation->project_name ?? $quotation->client_name ?? 'AMBALA AIRFORCE',
        ];
        return new QuotationHeaderDTO($data);
    }

    public function buildSummary(Quotation $quotation): QuotationSummaryDTO
    {
        $noOfComponents = $quotation->no_of_components;
        if ($noOfComponents <= 0 && $quotation->items) {
            $noOfComponents = $quotation->items->sum(function ($item) {
                return $item->qty ?? $item->quantity ?? 1;
            });
        }

        $totalArea = $quotation->total_area_sqft;
        if ($totalArea <= 0 && $quotation->items) {
            $totalArea = $quotation->items->sum('area');
        }

        $basicValue = (float)($quotation->basic_value ?? $quotation->subtotal ?? 0);
        $grandTotal = (float)($quotation->grand_total ?? 0);

        $avgExGst = $totalArea > 0 ? round($basicValue / $totalArea, 2) : (float)$quotation->avg_price_sqft_ex_gst;
        $avgIncGst = $totalArea > 0 ? round($grandTotal / $totalArea, 2) : (float)$quotation->avg_price_sqft_inc_gst;

        $data = [
            'no_of_components' => $noOfComponents,
            'total_area_sqft' => $totalArea,
            'basic_value' => $basicValue,
            'total_project_cost' => (float)($quotation->total_project_cost ?? $basicValue),
            'avg_price_sqft_ex_gst' => $avgExGst,
            'avg_price_sqft_inc_gst' => $avgIncGst,
        ];
        return new QuotationSummaryDTO($data);
    }

    public function buildFinancials(Quotation $quotation): FinancialSummaryDTO
    {
        $grandTotal = (float)($quotation->grand_total ?? 0);
        $amountInWords = $quotation->amount_in_words ?? $this->calculator->amountInWords($grandTotal);

        $data = [
            'subtotal' => (float)($quotation->subtotal ?? 0),
            'discount' => (float)($quotation->discount ?? 0),
            'discount_percent' => (float)($quotation->discount_percent ?? 0),
            'transportation' => (float)($quotation->transportation ?? $quotation->freight_charges ?? 0),
            'installation' => (float)($quotation->installation ?? $quotation->installation_cost ?? 0),
            'gst_percent' => (float)($quotation->tax_percent ?? 18),
            'gst' => (float)($quotation->gst ?? $quotation->tax_amount ?? 0),
            'additional_charges' => (float)($quotation->additional_charges ?? 0),
            'grand_total' => $grandTotal,
            'amount_in_words' => $amountInWords,
        ];
        return new FinancialSummaryDTO($data);
    }

    public function buildItems(Quotation $quotation): array
    {
        $items = [];
        if (!$quotation->items) {
            return $items;
        }

        foreach ($quotation->items as $index => $item) {
            $itemData = $item->toArray();
            
            // Generate SVG drawing
            $w = $item->width ?? $item->dimension_w ?? 1000;
            $h = $item->height ?? $item->dimension_h ?? 1000;
            $unit = $item->unit ?? 'mm';
            $system = $item->profile_system ?? 'Casement Series';
            $metadata = $item->drawing_metadata ?? [];

            $svgHtml = SvgGenerator::generateWindowDrawing($w, $h, $unit, $system, $metadata);
            $drawing = new DrawingDTO($svgHtml, 'View From Inside', $metadata);

            $profile = new ProfileDTO($item->profile_details ?? [], $system);
            $glass = new GlassDTO($itemData);
            $hardware = new HardwareDTO($itemData);
            $accessory = new AccessoryDTO($item->accessories_details ?? [], $system);

            $itemData['item_code'] = $item->item_code ?? ('W' . ($index + 1));
            $itemData['position'] = $item->position ?? $itemData['item_code'];

            $items[] = new ItemDTO($itemData, $drawing, $profile, $glass, $hardware, $accessory);
        }

        return $items;
    }

    public function buildTerms(Quotation $quotation): TermsDTO
    {
        $termsArray = is_array($quotation->terms_conditions) ? $quotation->terms_conditions : [];
        $setting = $quotation->companySetting ?? CompanySetting::first();
        $prereqs = $setting && is_array($setting->installation_prerequisites) ? $setting->installation_prerequisites : [];

        return new TermsDTO($termsArray, $prereqs);
    }

    public function buildBank(Quotation $quotation): BankDTO
    {
        $bankDetails = is_array($quotation->bank_details) ? $quotation->bank_details : [];
        return new BankDTO($bankDetails);
    }

    public function buildSignature(Quotation $quotation): SignatureDTO
    {
        $setting = $quotation->companySetting ?? CompanySetting::first();
        $sigImage = $setting ? ($setting->authorized_signature ?? '') : '';
        return new SignatureDTO('Authorized Signatory', 'Signature of Customer', $sigImage);
    }

    public function buildFooter(Quotation $quotation): FooterDTO
    {
        return new FooterDTO('{PAGE_NUM} of {PAGE_COUNT}', 'powered by Codevora Tech');
    }
}
