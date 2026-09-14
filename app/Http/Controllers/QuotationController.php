<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Http\Requests\QuotationRequest;
use App\Services\QuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    protected $quotationService;
    protected $pdfService;

    public function __construct(QuotationService $quotationService, \App\Services\PDFService $pdfService)
    {
        $this->quotationService = $quotationService;
        $this->pdfService = $pdfService;
    }

    public function index()
    {
        $quotations = Quotation::orderBy('id', 'desc')->get();
        return \App\Http\Resources\QuotationResource::collection($quotations);
    }

    public function store(QuotationRequest $request)
    {
        $validated = $request->validated();
        
        $quotation = $this->quotationService->saveDraft($validated);

        return response()->json(new \App\Http\Resources\QuotationResource($quotation), 201);
    }

    public function calculatePreview(QuotationRequest $request)
    {
        $validated = $request->validated();
        // Since calculateQuotationData was moved to QuotationCalculationService but exposed via QuotationService
        // Let's call it via quotationService or inject QuotationCalculationService
        $data = $this->quotationService->calculateQuotationData($validated);
        return response()->json($data);
    }

    public function show($id)
    {
        $quotation = Quotation::with('items.sizes')->findOrFail($id);
        $this->authorize('view', $quotation);
        return new \App\Http\Resources\QuotationResource($quotation);
    }

    public function update(QuotationRequest $request, $id)
    {
        $quotation = Quotation::findOrFail($id);
        $this->authorize('update', $quotation);
        $validated = $request->validated();
        
        $quotation = $this->quotationService->updateQuotation($quotation, $validated);

        return response()->json(new \App\Http\Resources\QuotationResource($quotation));
    }

    public function destroy($id)
    {
        $quotation = Quotation::findOrFail($id);
        $this->authorize('delete', $quotation);
        $quotation->delete();
        return response()->json(['success' => true]);
    }

    public function uploadDrawing(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('drawings', $filename, 'public');
            
            // Generate clean full URL accessible by frontend client
            $url = asset('storage/' . $path);
            
            return response()->json([
                'url' => $url,
                'path' => '/storage/' . $path
            ]);
        }

        return response()->json(['error' => 'File not uploaded'], 400);
    }

    public function generatePdf($id)
    {
        try {
            $quotation = Quotation::with(['items.sizes', 'customer', 'companySetting'])->findOrFail($id);
            $this->authorize('view', $quotation);
            $filePath = $this->pdfService->generateQuotationPDF($quotation);
            $url = url('/api/quotations/' . $id . '/preview'); // Using authenticated API route

            return response()->json([
                'success' => true,
                'message' => 'PDF generated successfully',
                'file_name' => basename($filePath),
                'url' => $url,
                'whatsapp_share_url' => 'https://api.whatsapp.com/send?text=' . urlencode('Hello, please find your quotation here: ' . $url),
                'email_share_url' => 'mailto:?subject=Quotation%20' . urlencode($quotation->quote_no) . '&body=' . urlencode('Hello, Please find your quotation attached via link: ' . $url)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate PDF: ' . $e->getMessage()
            ], 500);
        }
    }

    public function previewPdf($id)
    {
        $quotation = Quotation::with(['items.sizes', 'customer', 'companySetting'])->findOrFail($id);
        $this->authorize('view', $quotation);
        return $this->pdfService->streamPDF($quotation);
    }

    public function downloadPdf($id)
    {
        $quotation = Quotation::with(['items.sizes', 'customer', 'companySetting'])->findOrFail($id);
        $this->authorize('view', $quotation);
        return $this->pdfService->downloadPDF($quotation);
    }
}
