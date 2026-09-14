<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\CompanySetting;
use App\DTOs\ValidationItemDTO;
use App\DTOs\ValidationResultDTO;

class QuotationValidationService
{
    protected QuotationReportBuilder $reportBuilder;

    public function __construct(QuotationReportBuilder $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * Validate complete commercial quotation against 7 Validation Groups.
     *
     * @param Quotation $quotation
     * @return ValidationResultDTO
     */
    public function validate(Quotation $quotation): ValidationResultDTO
    {
        if ($quotation->exists) {
            $quotation->load(['items.sizes', 'customer', 'companySetting']);
        }
        $company = $quotation->companySetting ?? CompanySetting::first() ?? new CompanySetting();
        $reportDto = $this->reportBuilder->build($quotation);

        $rules = [];

        // -------------------------------------------------------------
        // GROUP 1: Customer Information
        // -------------------------------------------------------------
        $clientName = $quotation->client_name ?: ($quotation->customer->name ?? '');
        $rules[] = new ValidationItemDTO(
            'cust_name', 'Customer Name', 'Customer Information',
            !empty($clientName), 'Customer name is missing'
        );

        $custPhone = $quotation->customer?->phone;
        $rules[] = new ValidationItemDTO(
            'cust_phone', 'Customer Contact Number', 'Customer Information',
            !empty($custPhone), 'Customer contact number is missing'
        );

        $projectLocation = $quotation->project_location ?: ($quotation->address ?: ($quotation->customer->address ?? ''));
        $rules[] = new ValidationItemDTO(
            'cust_address', 'Project Site Address', 'Customer Information',
            !empty($projectLocation), 'Project site location address is missing'
        );

        $custEmail = $quotation->customer?->email;
        $rules[] = new ValidationItemDTO(
            'cust_email', 'Customer Email Address', 'Customer Information',
            !empty($custEmail) || !empty($custPhone), 'Customer email is missing'
        );

        $rules[] = new ValidationItemDTO(
            'proj_name', 'Project Name', 'Customer Information',
            !empty($quotation->project_name), 'Project name is missing'
        );

        $rules[] = new ValidationItemDTO(
            'sales_exec', 'Sales Executive / Signatory', 'Customer Information',
            !empty($quotation->sales_person ?? $reportDto->project->salesPerson), 'Sales executive is missing'
        );

        $rules[] = new ValidationItemDTO(
            'quote_date', 'Quotation Date', 'Customer Information',
            !empty($quotation->quotation_date ?? $quotation->date), 'Quotation date is missing'
        );

        $rules[] = new ValidationItemDTO(
            'valid_until', 'Validity Expiry Date', 'Customer Information',
            !empty($quotation->valid_until ?? $quotation->valid_till), 'Quotation validity date is missing'
        );

        // -------------------------------------------------------------
        // GROUP 2: Product Specifications (Per Item)
        // -------------------------------------------------------------
        $hasItems = $quotation->items && $quotation->items->count() > 0;
        $rules[] = new ValidationItemDTO(
            'prod_has_items', 'Quotation Items Exist', 'Product Specifications',
            $hasItems, 'Quotation contains no product items'
        );

        $allProfileBrand = true;
        $allGlassSpecs = true;
        $allHardwareBrand = true;
        $allDimsValid = true;

        if ($hasItems) {
            foreach ($quotation->items as $item) {
                if (empty($item->profile_system)) $allProfileBrand = false;
                if (empty($item->glass_type)) $allGlassSpecs = false;
                if (empty($item->hardware_brand)) $allHardwareBrand = false;
                if (($item->width ?? 0) <= 0 || ($item->height ?? 0) <= 0) $allDimsValid = false;
            }
        } else {
            $allProfileBrand = $allGlassSpecs = $allHardwareBrand = $allDimsValid = false;
        }

        $rules[] = new ValidationItemDTO(
            'prod_profile_brand', 'Profile Brand & System Series', 'Product Specifications',
            $allProfileBrand, 'One or more items are missing Profile Brand or Series'
        );

        $rules[] = new ValidationItemDTO(
            'prod_glass_specs', 'Glass Specifications & Make', 'Product Specifications',
            $allGlassSpecs, 'One or more items are missing Glass Specifications'
        );

        $rules[] = new ValidationItemDTO(
            'prod_hardware_brand', 'Hardware Brand & Specs', 'Product Specifications',
            $allHardwareBrand, 'One or more items are missing Hardware Specifications'
        );

        $rules[] = new ValidationItemDTO(
            'prod_dimensions', 'Item Dimensions (Width x Height)', 'Product Specifications',
            $allDimsValid, 'One or more items have invalid width or height'
        );

        // -------------------------------------------------------------
        // GROUP 3: Commercial Calculations
        // -------------------------------------------------------------
        $rules[] = new ValidationItemDTO(
            'calc_total_area', 'Total Area (Sq.Ft. > 0)', 'Commercial Calculations',
            ($quotation->total_area_sqft ?? 0) > 0, 'Total Area must be greater than zero'
        );

        $rules[] = new ValidationItemDTO(
            'calc_subtotal', 'Basic Subtotal (> 0)', 'Commercial Calculations',
            ($quotation->subtotal ?? $quotation->basic_value ?? 0) > 0, 'Basic subtotal must be greater than zero'
        );

        $rules[] = new ValidationItemDTO(
            'calc_grand_total', 'Grand Total Correct', 'Commercial Calculations',
            ($quotation->grand_total ?? 0) > 0, 'Grand Total must be greater than zero'
        );

        $rules[] = new ValidationItemDTO(
            'calc_amount_words', 'Amount in Words Generated', 'Commercial Calculations',
            !empty($quotation->amount_in_words ?? $reportDto->financials->amountInWords), 'Amount in words is missing'
        );

        $rules[] = new ValidationItemDTO(
            'calc_avg_rate', 'Average Rate per Sq.Ft. Generated', 'Commercial Calculations',
            !empty($reportDto->summary->formattedAvgPriceSqftIncGst), 'Average Sq.Ft. rate calculation missing'
        );

        // -------------------------------------------------------------
        // GROUP 4: Commercial Scope & Legal Terms
        // -------------------------------------------------------------
        $rules[] = new ValidationItemDTO(
            'scope_warranty', 'Product Warranty Defined', 'Commercial Scope',
            !empty($reportDto->terms->terms), 'Product Warranty terms are missing'
        );

        $rules[] = new ValidationItemDTO(
            'scope_site_readiness', 'Site Readiness Checklist Present', 'Commercial Scope',
            !empty($reportDto->terms->siteReadinessChecklist), 'Site readiness checklist is missing'
        );

        $rules[] = new ValidationItemDTO(
            'scope_qa_clause', 'Quality Assurance & Variation Order Clause', 'Commercial Scope',
            !empty($reportDto->terms->qualityAssurance) && !empty($reportDto->terms->variationOrderClause), 'Quality assurance commitment clause missing'
        );

        // -------------------------------------------------------------
        // GROUP 5: Document & Company Validation
        // -------------------------------------------------------------
        $rules[] = new ValidationItemDTO(
            'doc_company_name', 'Company Profile & Address', 'Document & Company',
            !empty($company->company_name) && !empty($company->address), 'Company profile address is missing'
        );

        $rules[] = new ValidationItemDTO(
            'doc_company_gst', 'Company GSTIN Number', 'Document & Company',
            !empty($company->gst_number ?? $company->gstin), 'Company GSTIN number is missing'
        );

        $rules[] = new ValidationItemDTO(
            'doc_bank_details', 'Payment Bank Details', 'Document & Company',
            !empty($company->bank_details ?? $reportDto->bank->bankDetails), 'Company bank payment details are missing'
        );

        $rules[] = new ValidationItemDTO(
            'doc_pdf_generated', 'EvA PDF Document Generated', 'Document & Company',
            !empty($quotation->pdf_path), 'PDF quotation document not generated yet'
        );

        // -------------------------------------------------------------
        // GROUP 6: Drawing Validation
        // -------------------------------------------------------------
        $allDrawingsValid = true;
        if ($hasItems) {
            foreach ($reportDto->items as $itemDto) {
                if (empty($itemDto->drawing->svgHtml)) {
                    $allDrawingsValid = false;
                }
            }
        } else {
            $allDrawingsValid = false;
        }

        $rules[] = new ValidationItemDTO(
            'draw_svg_present', 'CAD Technical SVG Drawings', 'Drawing Validation',
            $allDrawingsValid, 'One or more items are missing CAD SVG drawings'
        );

        // -------------------------------------------------------------
        // Calculate Overall Score Percentage & Group Scores
        // -------------------------------------------------------------
        $totalRules = count($rules);
        $passedRules = count(array_filter($rules, fn($r) => $r->passed));
        $scorePercent = (int)round(($passedRules / max(1, $totalRules)) * 100);

        // Calculate Group Scores
        $groups = [];
        foreach ($rules as $r) {
            if (!isset($groups[$r->group])) {
                $groups[$r->group] = ['total' => 0, 'passed' => 0];
            }
            $groups[$r->group]['total']++;
            if ($r->passed) $groups[$r->group]['passed']++;
        }

        $groupScores = [];
        foreach ($groups as $gName => $gData) {
            $groupScores[$gName] = (int)round(($gData['passed'] / max(1, $gData['total'])) * 100);
        }

        return new ValidationResultDTO($scorePercent, $rules, $groupScores);
    }
}
