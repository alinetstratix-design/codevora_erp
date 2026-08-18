<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\CompanySetting;
use App\Models\Product;
use App\DTOs\QuotationReportDTO;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    protected QuotationCalculator $calculator;
    protected PDFService $pdfService;
    protected QuotationReportBuilder $reportBuilder;

    public function __construct(QuotationCalculator $calculator, PDFService $pdfService, QuotationReportBuilder $reportBuilder)
    {
        $this->calculator = $calculator;
        $this->pdfService = $pdfService;
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * Build full QuotationReportDTO for reports, previews, APIs, or exports.
     */
    public function getReportDTO(Quotation $quotation): QuotationReportDTO
    {
        return $this->reportBuilder->build($quotation);
    }

    /**
     * Calculate and prepare complete quotation data matching EvA specifications.
     */
    public function calculateQuotationData(array $validated): array
    {
        $items = $validated['items'] ?? [];
        $discount = (float)($validated['discount'] ?? $validated['discount_amount'] ?? 0);
        $transportation = (float)($validated['transportation'] ?? $validated['freight_charges'] ?? 0);
        $installation = (float)($validated['installation'] ?? $validated['installation_cost'] ?? 0);
        $gstPercent = (float)($validated['gst_percent'] ?? $validated['tax_percent'] ?? 18);

        // Process raw items and invoke calculator
        $totals = $this->calculator->calculateTotals($items, $discount, $transportation, $installation, $gstPercent);

        $processedItems = [];
        $itemCounter = 1;

        foreach ($totals['items'] as $index => $itemData) {
            $code = $itemData['item_code'] ?? $itemData['code'] ?? ('W' . $itemCounter);
            $profileSystem = $itemData['profile_system'] ?? null;
            
            // Build profile details JSON dynamically
            $profileDetails = $itemData['profile_details'] ?? [];
            if (empty($profileDetails) && isset($itemData['profile_color'])) {
                $profileDetails['Profile Color'] = $itemData['profile_color'];
            }

            // Build accessories details JSON dynamically
            $accessoriesDetails = $itemData['accessories_details'] ?? [];

            // Drawing metadata for vector SVG engine dynamically
            $drawingMetadata = $itemData['drawing_metadata'] ?? [];

            $w = (float)($itemData['dimension_w'] ?? ($itemData['sizes'][0]['width'] ?? 1000));
            $h = (float)($itemData['dimension_h'] ?? ($itemData['sizes'][0]['height'] ?? 1000));

            $colorVal = $itemData['color'] ?? $itemData['profile_color'] ?? 'WHITE';

            $processedItems[] = [
                'product_id' => $itemData['product_id'] ?? null,
                'item_code' => $code,
                'position' => $itemData['position'] ?? ('W' . $itemCounter),
                'product_name' => $itemData['product_name'] ?? $itemData['system_name'] ?? ('Window ' . $code),
                'system_name' => $itemData['system_name'] ?? $itemData['product_name'] ?? ('Window ' . $code),
                'profile_system' => $profileSystem,
                'profile_brand' => $itemData['profile_brand'] ?? null,
                'profile_series' => $itemData['profile_series'] ?? null,
                'opening_type' => $itemData['opening_type'] ?? null,
                'glass_type' => $itemData['glass_type'] ?? null,
                'glass_thickness' => $itemData['glass_thickness'] ?? null,
                'hardware_brand' => $itemData['hardware_brand'] ?? null,
                'mesh_type' => $itemData['mesh_type'] ?? 'No',
                'color' => $colorVal,
                'profile_color' => $colorVal,
                'handle_type' => $itemData['handle_type'] ?? 'C-Type Handle',
                'hardware_color' => $itemData['hardware_color'] ?? 'WHITE',
                'dimension_w' => $w,
                'dimension_h' => $h,
                'width' => $w,
                'height' => $h,
                'unit' => $itemData['unit'] ?? 'mm',
                'qty' => (int)($itemData['qty'] ?? 1),
                'quantity' => (int)($itemData['qty'] ?? 1),
                'area' => (float)($itemData['area'] ?? 0),
                'weight_kg' => (float)($itemData['weight_kg'] ?? 0),
                'unit_price' => (float)($itemData['unit_price'] ?? 0),
                'value_per_sqft' => (float)($itemData['value_per_sqft'] ?? 0),
                'rate' => (float)($itemData['rate'] ?? 0),
                'amount' => (float)($itemData['amount'] ?? 0),
                'total_cost' => (float)($itemData['amount'] ?? 0),
                'notes' => $itemData['notes'] ?? $itemData['remarks'] ?? null,
                'profile_details' => $profileDetails,
                'accessories_details' => $accessoriesDetails,
                'drawing_metadata' => $drawingMetadata,
                'sort_order' => $itemCounter,
                'sizes' => $itemData['sizes'] ?? []
            ];

            $itemCounter++;
        }

        $quoteNo = $validated['quote_no'] ?? $validated['quotation_number'] ?? ('SCL-QT-' . str_pad(rand(1, 99999), 8, '0', STR_PAD_LEFT));

        $company = CompanySetting::first();

        return [
            'quotation_data' => [
                'quotation_number' => $quoteNo,
                'quote_no' => $quoteNo,
                'quotation_date' => $validated['date'] ?? $validated['quotation_date'] ?? date('Y-m-d'),
                'valid_until' => $validated['valid_till'] ?? $validated['valid_until'] ?? date('Y-m-d', strtotime('+30 days')),
                'customer_id' => $validated['customer_id'] ?? null,
                'company_setting_id' => $company ? $company->id : null,
                'client_name' => $validated['client_name'] ?? null,
                'project_name' => $validated['project_name'] ?? null,
                'project_location' => $validated['project_location'] ?? $validated['address'] ?? null,
                'address' => $validated['address'] ?? $validated['project_location'] ?? null,
                'sales_person' => $validated['sales_person'] ?? null,
                'remarks' => $validated['remarks'] ?? null,

                'no_of_components' => $totals['no_of_components'],
                'total_area_sqft' => $totals['total_area_sqft'],
                'basic_value' => $totals['basic_value'],
                'subtotal' => $totals['subtotal'],
                'discount' => $totals['discount'],
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'transportation' => $totals['transportation'],
                'installation' => $totals['installation'],
                'freight_charges' => $totals['transportation'],
                'installation_cost' => $totals['installation'],
                'tax_percent' => $totals['gst_percent'],
                'tax_amount' => $totals['gst'],
                'gst' => $totals['gst'],
                'total_project_cost' => $totals['total_project_cost'],
                'grand_total' => $totals['grand_total'],
                'avg_price_sqft_ex_gst' => $totals['avg_price_sqft_ex_gst'],
                'avg_price_sqft_inc_gst' => $totals['avg_price_sqft_inc_gst'],
                'amount_in_words' => $totals['amount_in_words'],

                'status' => $validated['status'] ?? 'Draft',
                'terms_conditions' => $validated['terms_conditions'] ?? ($company->terms_conditions ?? null),
                'bank_details' => $validated['bank_details'] ?? ($company->bank_details ?? null),
                'cover_letter_enclosures' => [
                    'a. Window design, specification and value',
                    'b. Terms and Conditions'
                ]
            ],
            'items' => $processedItems
        ];
    }

    /**
     * Save quotation draft and its items.
     */
    public function saveDraft(array $data): Quotation
    {
        $calculated = $this->calculateQuotationData($data);

        return DB::transaction(function () use ($calculated) {
            $quotation = Quotation::create($calculated['quotation_data']);

            foreach ($calculated['items'] as $itemData) {
                $sizes = $itemData['sizes'] ?? [];
                unset($itemData['sizes']);

                $item = $quotation->items()->create($itemData);

                if (!empty($sizes)) {
                    foreach ($sizes as $sizeData) {
                        $item->sizes()->create($sizeData);
                    }
                }
            }

            return $quotation->fresh(['items.sizes', 'customer', 'companySetting']);
        });
    }

    /**
     * Generate PDF and update quotation record with stored PDF path.
     */
    public function generateAndAttachPDF(Quotation $quotation): string
    {
        $pdfPath = $this->pdfService->generateQuotationPDF($quotation);
        $quotation->update(['pdf_path' => $pdfPath]);
        return $pdfPath;
    }
}
