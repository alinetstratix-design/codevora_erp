<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Product;
use App\Services\QuotationService;
use App\Services\QuotationCalculator;
use App\Services\PDFService;
use App\Services\BomEngine;
use App\Services\QuotationValidationService;
use App\Http\Requests\QuotationRequest;
use Illuminate\Http\Request;

class QuotationWebController extends Controller
{
    protected QuotationService $quotationService;
    protected QuotationCalculator $calculator;
    protected PDFService $pdfService;
    protected BomEngine $bomEngine;
    protected QuotationValidationService $validationService;

    public function __construct(
        QuotationService $quotationService,
        QuotationCalculator $calculator,
        PDFService $pdfService,
        BomEngine $bomEngine,
        QuotationValidationService $validationService
    ) {
        $this->quotationService = $quotationService;
        $this->calculator = $calculator;
        $this->pdfService = $pdfService;
        $this->bomEngine = $bomEngine;
        $this->validationService = $validationService;
    }

    public function index()
    {
        $quotations = Quotation::with('customer')->orderBy('id', 'desc')->paginate(10);
        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'Active')->get();
        $products = Product::where('status', 'Active')->get();
        $company = \App\Models\CompanySetting::first();
        return view('quotations.create', compact('customers', 'products', 'company'));
    }

    public function calculate(Request $request)
    {
        $items = $request->input('items', []);
        $discount = $request->input('discount', 0);
        $transportation = $request->input('transportation', 0);
        $installation = $request->input('installation', 0);
        $gstPercent = $request->input('gst_percent', 18);

        $totals = $this->calculator->calculateTotals($items, $discount, $transportation, $installation, $gstPercent);
        
        return response()->json($totals);
    }

    public function store(QuotationRequest $request)
    {
        // 1. Save Quotation Draft
        $quotation = $this->quotationService->saveDraft($request->validated());

        // 2. Generate PDF & Attach Path
        $this->quotationService->generateAndAttachPDF($quotation);

        return redirect()->route('quotations.show', $quotation->id)->with('success', 'Quotation draft created and EvA PDF generated successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.sizes', 'customer', 'companySetting']);
        $validationResult = $this->validationService->validate($quotation);
        
        return view('quotations.show', compact('quotation', 'validationResult'));
    }

    public function validateAjax(Quotation $quotation)
    {
        $quotation->load(['items.sizes', 'customer', 'companySetting']);
        $validationResult = $this->validationService->validate($quotation);

        return response()->json($validationResult->toArray());
    }

    public function approve(Quotation $quotation)
    {
        $this->authorize('approve', $quotation);

        $quotation->load(['items.sizes', 'customer', 'companySetting']);
        $validationResult = $this->validationService->validate($quotation);

        // Block approval if score is less than 100%
        if (!$validationResult->canApprove) {
            $failedMsgs = implode(', ', array_map(fn($f) => $f->label, $validationResult->failedChecks));
            return redirect()->back()->with('error', "Quotation cannot be approved! Commercial Validation score is {$validationResult->scorePercent}%. Fix missing items: {$failedMsgs}");
        }

        $quotation->update(['status' => 'Approved']);

        return redirect()->route('quotations.show', $quotation->id)->with('success', 'Quotation successfully validated 100% and Approved.');
    }

    public function previewPdf(Quotation $quotation)
    {
        return $this->pdfService->streamPDF($quotation);
    }

    public function downloadPdf(Quotation $quotation)
    {
        return $this->pdfService->downloadPDF($quotation);
    }

    public function showBom(Quotation $quotation)
    {
        $quotation->load(['items']);
        $boms = [];

        foreach ($quotation->items as $item) {
            $boms[] = $this->bomEngine->generateBOM($item->toArray());
        }

        return view('quotations.bom', compact('quotation', 'boms'));
    }
}
