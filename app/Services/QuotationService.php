<?php

namespace App\Services;

use App\Constants\QuotationStatus;
use App\Models\Quotation;
use App\DTOs\QuotationReportDTO;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
     * Calculate and prepare complete quotation data.
     */
    public function calculateQuotationData(array $validated): array
    {
        return $this->calculator->calculateQuotationData($validated);
    }

    /**
     * Delete = soft delete. The quotation, its items, BOM snapshots and PDF stay in the database
     * and on disk; only visibility is removed. Who deleted it and when is kept on the row and in
     * the audit log. The quotation number stays reserved (nextQuotationNumber() counts trashed rows).
     */
    public function deleteQuotation(Quotation $quotation, ?int $userId = null): void
    {
        DB::transaction(function () use ($quotation, $userId) {
            $quotation->forceFill(['deleted_by' => $userId])->saveQuietly();
            $quotation->delete();

            \App\Models\AuditLog::record('quotation.deleted', $quotation, $quotation->quotation_number, [
                'status' => $quotation->status,
                'client' => $quotation->client_name,
                'project' => $quotation->project_name,
                'grand_total' => $quotation->grand_total,
            ]);
        });
    }

    /**
     * Next quotation number for a company, e.g. QT-20261012-0001.
     * The unique index on (company_id, quotation_number) guarantees no duplicates.
     */
    public function nextQuotationNumber(?int $companyId): string
    {
        $prefix = 'QT-' . now()->format('Ymd') . '-';

        $last = Quotation::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('quotation_number', 'like', $prefix . '%')
            ->orderByDesc('quotation_number')
            ->value('quotation_number');

        $sequence = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Save quotation draft and its items. Quotation numbers are always assigned by the server.
     *
     * @throws ValidationException when the BOM is incomplete (prices would be wrong)
     */
    public function saveDraft(array $data, ?int $companyId): Quotation
    {
        $calculated = $this->assertBomComplete($this->calculateQuotationData($data));

        $attempts = 0;
        while (true) {
            try {
                return DB::transaction(function () use ($calculated, $companyId) {
                    $quotationData = $calculated['quotation_data'];
                    $quotationData['quotation_number'] = $this->nextQuotationNumber($companyId);
                    $quotationData['company_id'] = $companyId;

                    $quotation = new Quotation();
                    $quotation->forceFill($quotationData);
                    $quotation->save();

                    $this->persistItems($quotation, $calculated['items']);

                    return $quotation->fresh(['items.sizes', 'items.boms', 'customer', 'companySetting']);
                });
            } catch (QueryException $e) {
                // Another request took the same number: retry with the next one.
                if (++$attempts >= 3 || !str_contains($e->getMessage(), 'quotations_company_number_unique')) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Update an existing quotation. Quotation number and workflow status are never changed here.
     *
     * @throws ValidationException when the BOM is incomplete
     */
    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        $calculated = $this->assertBomComplete($this->calculateQuotationData($data));

        return DB::transaction(function () use ($quotation, $calculated) {
            // Lock the row so a concurrent approval cannot slip in between the check and the write.
            $quotation = Quotation::query()->whereKey($quotation->id)->lockForUpdate()->firstOrFail();
            if ($quotation->status === QuotationStatus::APPROVED) {
                throw ValidationException::withMessages(['status' => ['Approved quotations cannot be edited.']]);
            }

            $quotationData = $calculated['quotation_data'];
            unset($quotationData['quotation_number'], $quotationData['status'], $quotationData['company_id']);

            $quotation->forceFill($quotationData)->save();

            $existingItemIds = $quotation->items->pluck('id')->toArray();
            $processedItemIds = [];

            foreach ($calculated['items'] as $itemData) {
                $sizes = $itemData['sizes'] ?? [];
                $bomResult = $itemData['bom_result'] ?? [];
                unset($itemData['sizes'], $itemData['bom_result']);

                $itemId = $itemData['id'] ?? null;

                if ($itemId && in_array($itemId, $existingItemIds)) {
                    $item = $quotation->items()->find($itemId);
                    $item->update($itemData);
                    $processedItemIds[] = $itemId;
                    $item->boms()->delete();
                } else {
                    unset($itemData['id']);
                    $item = $quotation->items()->create($itemData);
                    $processedItemIds[] = $item->id;
                }

                $existingSizeIds = $item->sizes()->pluck('id')->toArray();
                $processedSizeIds = [];

                foreach ($sizes as $sizeData) {
                    $sizeId = $sizeData['id'] ?? null;
                    if ($sizeId && in_array($sizeId, $existingSizeIds)) {
                        $size = $item->sizes()->find($sizeId);
                        $size->update($sizeData);
                        $processedSizeIds[] = $sizeId;
                    } else {
                        unset($sizeData['id']);
                        $size = $item->sizes()->create($sizeData);
                        $processedSizeIds[] = $size->id;
                    }
                }

                $sizesToDelete = array_diff($existingSizeIds, $processedSizeIds);
                if (!empty($sizesToDelete)) {
                    $item->sizes()->whereIn('id', $sizesToDelete)->delete();
                }

                $this->persistBoms($item, $bomResult);
            }

            $itemsToDelete = array_diff($existingItemIds, $processedItemIds);
            if (!empty($itemsToDelete)) {
                foreach ($quotation->items()->whereIn('id', $itemsToDelete)->get() as $obsolete) {
                    $obsolete->boms()->delete();
                }
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
        $previousPath = $quotation->pdf_path;

        $pdfPath = $this->pdfService->generateQuotationPDF($quotation);
        $quotation->update(['pdf_path' => $pdfPath, 'pdf_stale' => false]);

        // Keep storage tidy: the superseded PDF is removed once the new one is saved.
        // Legacy rows ('storage/...') live on the public disk and are removed from there.
        if ($previousPath && $previousPath !== $pdfPath) {
            if (str_starts_with($previousPath, 'storage/')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete(substr($previousPath, strlen('storage/')));
            } else {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($previousPath);
            }
        }

        return $pdfPath;
    }

    private function assertBomComplete(array $calculated): array
    {
        if (!empty($calculated['bom_errors'])) {
            throw ValidationException::withMessages([
                'items' => array_merge(
                    ['The costing for this quotation is incomplete. Fix the product BOM configuration and try again:'],
                    $calculated['bom_errors']
                ),
            ]);
        }

        return $calculated;
    }

    private function persistItems(Quotation $quotation, array $items): void
    {
        foreach ($items as $itemData) {
            $sizes = $itemData['sizes'] ?? [];
            $bomResult = $itemData['bom_result'] ?? [];
            unset($itemData['sizes'], $itemData['bom_result'], $itemData['id']);

            $item = $quotation->items()->create($itemData);

            foreach ($sizes as $sizeData) {
                unset($sizeData['id']);
                $item->sizes()->create($sizeData);
            }

            $this->persistBoms($item, $bomResult);
        }
    }

    private function persistBoms($item, array $bomResult): void
    {
        foreach ($bomResult['items'] ?? [] as $bomLine) {
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
