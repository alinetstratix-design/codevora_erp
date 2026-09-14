<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Customer;
use App\Models\Product;
use App\Services\QuotationService;
use App\Services\PDFService;
use App\Services\QuotationValidationService;
use App\Http\Requests\QuotationRequest;
use Illuminate\Http\Request;

class QuotationWebController extends Controller
{
    protected QuotationService $quotationService;
    protected PDFService $pdfService;
    protected QuotationValidationService $validationService;

    public function __construct(
        QuotationService $quotationService,
        PDFService $pdfService,
        QuotationValidationService $validationService
    ) {
        $this->quotationService = $quotationService;
        $this->pdfService = $pdfService;
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
        $lookups = \App\Models\Lookup::where('is_active', true)->get()->groupBy('type');
        return view('quotations.create', compact('customers', 'products', 'company', 'lookups'));
    }

    public function calculate(Request $request)
    {
        $data = $request->all();
        
        // Use the unified calculation engine established in Phase D
        $calculationService = app(\App\Services\QuotationCalculationService::class);
        $totals = $calculationService->calculateQuotationData($data);
        
        return response()->json($totals);
    }

    public function store(QuotationRequest $request)
    {
        $validated = $request->validated();
        
        $idempKey = $validated['idempotency_key'] ?? null;
        if ($idempKey) {
            // Attempt atomic reservation for 60 seconds
            if (!\Illuminate\Support\Facades\Cache::add('quote_idemp_lock_' . $idempKey, true, 60)) {
                $existingQuoteId = \Illuminate\Support\Facades\Cache::get('quote_idemp_val_' . $idempKey);
                if ($existingQuoteId) {
                    if ($request->wantsJson()) {
                        return response()->json(['id' => $existingQuoteId]);
                    }
                    return redirect()->route('quotations.show', $existingQuoteId)
                        ->with('success', 'Quotation draft created and EvA PDF generated successfully.');
                }
                // Concurrent request is still processing, reject safely
                abort(409, 'Duplicate request is currently processing.');
            }
        }

        $savedFiles = [];
        
        // Removed base64 drawings processing logic (Goal #9). Geometry is now purely driven by backend SvgGenerator.

        try {
            // 1. Save Quotation Draft
            $quotation = $this->quotationService->saveDraft($validated);

            // 2. Generate PDF & Attach Path
            $this->quotationService->generateAndAttachPDF($quotation);
            
            if (isset($idempKey)) {
                \Illuminate\Support\Facades\Cache::put('quote_idemp_val_' . $idempKey, $quotation->id, now()->addMinutes(15));
            }

            if ($request->wantsJson()) {
                return response()->json(['id' => $quotation->id]);
            }

            return redirect()->route('quotations.show', $quotation->id)->with('success', 'Quotation draft created and EvA PDF generated successfully.');

        } catch (\Exception $e) {
            // File rollback on DB transaction failure
            foreach ($savedFiles as $file) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                }
            }
            
            if (isset($idempKey)) {
                \Illuminate\Support\Facades\Cache::forget('quote_idemp_lock_' . $idempKey);
            }
            
            throw $e;
        }
    }

    public function edit(Quotation $quotation)
    {
        $quotation->load(['items.sizes', 'customer']);
        
        // Auto-heal customer_id if quotation was saved with missing customer_id but matching client_name
        if (empty($quotation->customer_id) && !empty($quotation->client_name)) {
            $baseName = trim(explode('(', $quotation->client_name)[0]);
            $matched = Customer::where('name', 'LIKE', '%' . $baseName . '%')->first();
            if ($matched) {
                $quotation->update(['customer_id' => $matched->id]);
                $quotation->load('customer');
            }
        }

        $customers = Customer::where('status', 'Active')->get();
        $products = Product::where('status', 'Active')->get();
        
        // Pass the preloaded quotation data to JavaScript
        $formattedDate = $quotation->quotation_date 
            ? (\Carbon\Carbon::parse($quotation->quotation_date)->format('Y-m-d')) 
            : date('Y-m-d');

        $quoteData = [
            'id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'project_name' => $quotation->project_name,
            'client_name' => $quotation->client_name,
            'quote_no' => $quotation->quotation_number ?? $quotation->quote_no,
            'date' => $formattedDate,
            'items' => $quotation->items->map(function($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name ?? $item->system_name,
                    'profile_brand' => $item->profile_brand ?? '',
                    'profile_series' => $item->profile_series ?? '',
                    'profile_system' => $item->profile_system ?? '',
                    'design_id' => $item->design_id,
                    'design_name' => $item->design ? $item->design->name : 'Custom',
                    'unit' => $item->unit ?? 'mm',
                    'drawing_b64' => $item->drawing_url ? $item->drawing_url : null,
                    'glass_type' => $item->glass_type ?? '',
                    'glass_thickness' => $item->glass_thickness ?? '',
                    'mesh_type' => $item->mesh_type ?? '',
                    'profile_color' => $item->profile_color ?? $item->color ?? '',
                    'hardware_color' => $item->hardware_color ?? '',
                    'hardware_brand' => $item->hardware_brand ?? '',
                    'handle_type' => $item->handle_type ?? '',
                    'remarks' => $item->remarks ?? $item->notes ?? '',
                    'profile_details' => $item->profile_details ?? [],
                    'accessories_details' => $item->accessories_details ?? [],
                    'sizes' => $item->sizes->map(function($size) use ($item) {
                        return [
                            'id' => $size->id,
                            'width' => (float)$size->width,
                            'height' => (float)$size->height,
                            'qty' => (int)$size->quantity,
                            'unit' => $size->unit ?? $item->unit ?? 'mm'
                        ];
                    })->toArray()
                ];
            })->toArray()
        ];
        
        return view('quotations.edit', compact('quotation', 'customers', 'products', 'quoteData'));
    }

    public function update(QuotationRequest $request, Quotation $quotation)
    {
        $validated = $request->validated();
        
        $savedFiles = [];
        
        $savedFiles = [];
        
        // Removed base64 drawings processing logic (Goal #9). Geometry is now purely driven by backend SvgGenerator.

        try {
            // 1. Update Quotation
            $quotation = $this->quotationService->updateQuotation($quotation, $validated);

            // 2. Generate PDF & Attach Path
            $this->quotationService->generateAndAttachPDF($quotation);

            if ($request->wantsJson()) {
                return response()->json(['id' => $quotation->id]);
            }

            return redirect()->route('quotations.show', $quotation->id)->with('success', 'Quotation updated and EvA PDF regenerated successfully.');

        } catch (\Exception $e) {
            // File rollback on DB transaction failure
            foreach ($savedFiles as $file) {
                if (\Illuminate\Support\Facades\Storage::disk('public')->exists($file)) {
                    \Illuminate\Support\Facades\Storage::disk('public')->delete($file);
                }
            }
            
            throw $e;
        }
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.sizes', 'customer', 'companySetting']);
        
        // Auto-heal customer_id if quotation was saved with missing customer_id but matching client_name
        if (empty($quotation->customer_id) && !empty($quotation->client_name)) {
            $baseName = trim(explode('(', $quotation->client_name)[0]);
            $matched = Customer::where('name', 'LIKE', '%' . $baseName . '%')->first();
            if ($matched) {
                $quotation->update(['customer_id' => $matched->id]);
                $quotation->load('customer');
            }
        }

        // Ensure PDF is generated and attached if missing
        if (empty($quotation->pdf_path) || !\Illuminate\Support\Facades\Storage::disk('public')->exists(str_replace('storage/', '', $quotation->pdf_path))) {
            try {
                $this->quotationService->generateAndAttachPDF($quotation);
                $quotation->refresh();
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Could not auto-generate PDF for quotation {$quotation->id}: " . $e->getMessage());
            }
        }

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
        $quotation->load(['items.boms.material', 'items.sizes']);
        
        $boms = [];
        foreach ($quotation->items as $item) {
            $profileCost = 0;
            $glassCost = 0;
            $steelCost = 0;
            $hardwareCost = 0;
            $accessoriesCost = 0;
            $lineItems = [];

            foreach ($item->boms as $bomLine) {
                $category = $bomLine->material ? $bomLine->material->category : 'Other';
                $cost = (float)$bomLine->total_cost;
                
                if (stripos($category, 'profile') !== false) {
                    $profileCost += $cost;
                } elseif (stripos($category, 'glass') !== false) {
                    $glassCost += $cost;
                } elseif (stripos($category, 'steel') !== false || stripos($category, 'reinforcement') !== false) {
                    $steelCost += $cost;
                } elseif (stripos($category, 'hardware') !== false) {
                    $hardwareCost += $cost;
                } else {
                    $accessoriesCost += $cost;
                }

                $lineItems[] = [
                    'material_category' => $category,
                    'material_name' => $bomLine->material_name,
                    'sku' => $bomLine->material_sku ?? '-',
                    'supplier' => $bomLine->material ? $bomLine->material->supplier_name : '-',
                    'unit' => $bomLine->material ? $bomLine->material->uom : 'nos',
                    'length' => 0, // Not explicitly stored per line unless in snapshot
                    'quantity' => $bomLine->calculated_qty,
                    'weight_kg' => ($bomLine->material && $bomLine->material->weight_per_unit) ? ($bomLine->material->weight_per_unit * $bomLine->calculated_qty) : 0,
                    'waste_percent' => $bomLine->material ? $bomLine->material->waste_percent : 0,
                    'rate' => $bomLine->unit_cost,
                    'total_cost' => $bomLine->total_cost,
                    'remarks' => $bomLine->rule_type,
                ];
            }

            $w = $item->width ?? $item->dimension_w ?? ($item->sizes->first()->width ?? 0);
            $h = $item->height ?? $item->dimension_h ?? ($item->sizes->first()->height ?? 0);
            $totalQty = $item->quantity ?? $item->qty ?? $item->sizes->sum('quantity') ?? 1;

            $boms[] = [
                'item_code' => $item->item_code ?? 'Item',
                'dimensions' => "{$w}x{$h}",
                'total_qty' => $totalQty,
                'total_area_sqft' => $item->area ?? $item->total_area_sqft ?? 0,
                'total_weight_kg' => $item->weight_kg ?? 0,
                'profile_cost' => $profileCost,
                'glass_cost' => $glassCost,
                'steel_cost' => $steelCost,
                'hardware_cost' => $hardwareCost,
                'accessories_cost' => $accessoriesCost,
                'total_bom_cost' => $profileCost + $glassCost + $steelCost + $hardwareCost + $accessoriesCost,
                'line_items' => $lineItems,
            ];
        }

        return view('quotations.bom', compact('quotation', 'boms'));
    }
}
