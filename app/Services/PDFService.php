<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Quotation;

class PDFService
{
    private const DISK = 'local';
    private const DIRECTORY = 'quotation_pdfs';

    protected QuotationReportBuilder $reportBuilder;

    public function __construct(QuotationReportBuilder $reportBuilder)
    {
        $this->reportBuilder = $reportBuilder;
    }

    /**
     * Generate PDF and save to disk.
     */
    public function generateQuotationPDF(Quotation $quotation): string
    {
        try {
            // Build the complete QuotationReportDTO
            $reportDto = $this->reportBuilder->build($quotation);
            $reportData = $reportDto->toArray();

            // Load PDF using DomPDF facade with custom options
            $pdf = Pdf::loadView('pdf.quotation', [
                'report' => $reportDto,
                'reportData' => $reportData,
                'company' => $quotation->companySetting ?? \App\Models\CompanySetting::forCompany($quotation->company_id)
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isPhpEnabled', true) // needed for the footer text/php script; values are var_export-escaped
            ->setOption('isRemoteEnabled', false)
            ->setOption('isHtml5ParserEnabled', true);

            // Define a clean relative filename
            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '_', $quotation->quotation_number ?? 'QT-' . $quotation->id);
            $fileName = self::DIRECTORY . '/' . $cleanName . '_' . \Illuminate\Support\Str::random(10) . '.pdf';

            // Stored on the PRIVATE disk (storage/app), never publicly reachable: quotations hold bank and client data.
            Storage::disk(self::DISK)->put($fileName, $pdf->output());

            Log::info("PDF successfully generated for Quotation ID: {$quotation->id} at disk path: {$fileName}");

            return $fileName;
        } catch (\Throwable $e) {
            Log::error("Failed to generate PDF for Quotation ID: {$quotation->id} - Error: " . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Get absolute physical filesystem path for a stored PDF.
     */
    public function getPhysicalPath(Quotation $quotation): string
    {
        // Legacy rows point to 'storage/quotations/...' on the public disk; those are regenerated
        // on the private disk so the old public copies are no longer the source of truth.
        $relativePath = (string) $quotation->pdf_path;
        $isPrivate = str_starts_with($relativePath, self::DIRECTORY . '/');

        if (!$isPrivate || $quotation->pdf_stale || !Storage::disk(self::DISK)->exists($relativePath)) {
            $relativePath = $this->generateQuotationPDF($quotation);
            $quotation->update(['pdf_path' => $relativePath, 'pdf_stale' => false]);
        }

        return Storage::disk(self::DISK)->path($relativePath);
    }

    /**
     * Return Inline Stream Response for Browser Preview.
     */
    public function streamPDF(Quotation $quotation)
    {
        $physicalPath = $this->getPhysicalPath($quotation);
        $fileName = basename($physicalPath);

        return response()->file($physicalPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"'
        ]);
    }

    /**
     * Return Force Download Response for Browser Download.
     */
    public function downloadPDF(Quotation $quotation)
    {
        $physicalPath = $this->getPhysicalPath($quotation);
        $fileName = ($quotation->quotation_number ?? $quotation->quote_no ?? 'Quotation') . '.pdf';

        return response()->download($physicalPath, $fileName, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
