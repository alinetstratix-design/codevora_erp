<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Quotation;

class PDFService
{
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
                'company' => $quotation->companySetting 
                    ?? \App\Models\CompanySetting::where('company_id', $quotation->company_id)->first() 
                    ?? \App\Models\CompanySetting::find($quotation->company_id) 
                    ?? \App\Models\CompanySetting::first()
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isPhpEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true);

            // Define a clean relative filename
            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '_', $quotation->quotation_number ?? $quotation->quote_no ?? 'QT-' . $quotation->id);
            $fileName = 'quotations/' . $cleanName . '_' . time() . '.pdf';
            
            // Save PDF to public storage disk
            Storage::disk('public')->put($fileName, $pdf->output());

            Log::info("PDF successfully generated for Quotation ID: {$quotation->id} at disk path: {$fileName}");

            return 'storage/' . $fileName;
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
        $relativePath = !empty($quotation->pdf_path) ? str_replace('storage/', '', $quotation->pdf_path) : '';
        if (empty($relativePath) || !Storage::disk('public')->exists($relativePath)) {
            $newPath = $this->generateQuotationPDF($quotation);
            $quotation->update(['pdf_path' => $newPath]);
            $relativePath = str_replace('storage/', '', $newPath);
        }

        return Storage::disk('public')->path($relativePath);
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
