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
    protected QuotationCalculationService $calculator;
    protected PDFService $pdfService;
    protected QuotationReportBuilder $reportBuilder;

    public function __construct(QuotationCalculationService $calculator, PDFService $pdfService, QuotationReportBuilder $reportBuilder)
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
        return $this->calculator->calculateQuotationData($validated);
    }

    /**
     * Save quotation draft and its items.
     */
    public function saveDraft(array $data): Quotation
    {
        $calculated = $this->calculateQuotationData($data);

        return DB::transaction(function () use ($calculated) {
            $quotation = new Quotation();
            $quotation->forceFill($calculated['quotation_data']);
            $quotation->save();

            foreach ($calculated['items'] as $itemData) {
                $sizes = $itemData['sizes'] ?? [];
                $bomResult = $itemData['bom_result'] ?? [];
                unset($itemData['sizes']);
                unset($itemData['bom_result']);
                unset($itemData['id']); // Ensure no rogue ID on create

                $item = $quotation->items()->create($itemData);

                if (!empty($sizes)) {
                    foreach ($sizes as $sizeData) {
                        unset($sizeData['id']); // Ensure no rogue ID on create
                        $item->sizes()->create($sizeData);
                    }
                }

                if (!empty($bomResult['items'])) {
                    foreach ($bomResult['items'] as $bomLine) {
                        $item->boms()->create([
                            'material_id' => $bomLine['material_id'],
                            'rule_type' => $bomLine['rule_type'],
                            'material_sku' => $bomLine['material_sku'],
                            'material_name' => $bomLine['material_name'],
                            'unit_cost' => $bomLine['unit_cost'],
                            'calculated_qty' => $bomLine['total_qty'],
                            'total_cost' => $bomLine['total_cost'],
                            'calculation_snapshot' => $bomLine['snapshot_context'] ?? [],
                        ]);
                    }
                }
            }

            return $quotation->fresh(['items.sizes', 'items.boms', 'customer', 'companySetting']);
        });
    }

    /**
     * Update an existing quotation and sync its items and sizes.
     */
    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        $calculated = $this->calculateQuotationData($data);

        return DB::transaction(function () use ($quotation, $calculated) {
            $quotation->forceFill($calculated['quotation_data'])->save();

            $existingItemIds = $quotation->items->pluck('id')->toArray();
            $processedItemIds = [];

            foreach ($calculated['items'] as $itemData) {
                $sizes = $itemData['sizes'] ?? [];
                $bomResult = $itemData['bom_result'] ?? [];
                unset($itemData['sizes']);
                unset($itemData['bom_result']);

                $itemId = $itemData['id'] ?? null;

                if ($itemId && in_array($itemId, $existingItemIds)) {
                    // Update existing item
                    $item = $quotation->items()->find($itemId);
                    $item->update($itemData);
                    $processedItemIds[] = $itemId;
                    // Delete old boms for this item to regenerate
                    $item->boms()->delete();
                } else {
                    // Create new item
                    $item = $quotation->items()->create($itemData);
                    $processedItemIds[] = $item->id;
                }

                // Sync sizes for this item
                $existingSizeIds = $item->sizes()->pluck('id')->toArray();
                $processedSizeIds = [];
                
                if (!empty($sizes)) {
                    foreach ($sizes as $sizeData) {
                        $sizeId = $sizeData['id'] ?? null;
                        if ($sizeId && in_array($sizeId, $existingSizeIds)) {
                            $size = $item->sizes()->find($sizeId);
                            $size->update($sizeData);
                            $processedSizeIds[] = $sizeId;
                        } else {
                            $size = $item->sizes()->create($sizeData);
                            $processedSizeIds[] = $size->id;
                        }
                    }
                }
                
                // Remove deleted sizes
                $sizesToDelete = array_diff($existingSizeIds, $processedSizeIds);
                if (!empty($sizesToDelete)) {
                    $item->sizes()->whereIn('id', $sizesToDelete)->delete();
                }

                // Insert regenerated BOMs
                if (!empty($bomResult['items'])) {
                    foreach ($bomResult['items'] as $bomLine) {
                        $item->boms()->create([
                            'material_id' => $bomLine['material_id'],
                            'rule_type' => $bomLine['rule_type'],
                            'material_sku' => $bomLine['material_sku'],
                            'material_name' => $bomLine['material_name'],
                            'unit_cost' => $bomLine['unit_cost'],
                            'calculated_qty' => $bomLine['total_qty'],
                            'total_cost' => $bomLine['total_cost'],
                            'calculation_snapshot' => $bomLine['snapshot_context'] ?? [],
                        ]);
                    }
                }
            }

            // Remove deleted items
            $itemsToDelete = array_diff($existingItemIds, $processedItemIds);
            if (!empty($itemsToDelete)) {
                $quotation->items()->whereIn('id', $itemsToDelete)->delete();
            }

            return $quotation->fresh(['items.sizes', 'items.boms', 'customer', 'companySetting']);
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
