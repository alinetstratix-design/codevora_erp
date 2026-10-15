<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\Customer;
use App\Models\Lookup;
use App\Models\Product;
use App\Models\Quotation;
use App\Http\Requests\QuotationRequest;
use App\Services\PDFService;
use App\Services\QuotationCalculationService;
use App\Services\QuotationService;
use App\Services\QuotationValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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

    public function index(Request $request)
    {
        $query = Quotation::with('customer')->orderBy('id', 'desc');

        if ($request->filled('status') && in_array($request->status, \App\Constants\QuotationStatus::all(), true)) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $term = '%' . trim($request->q) . '%';
            // Grouped, so the company scope still applies to the whole search.
            $query->where(function ($q) use ($term) {
                $q->where('quotation_number', 'like', $term)
                  ->orWhere('client_name', 'like', $term)
                  ->orWhere('project_name', 'like', $term);
            });
        }

        $quotations = $query->paginate(10)->withQueryString();

        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::where('status', 'Active')->get();
        $products = Product::where('status', 'Active')->get();
        $company = CompanySetting::forCompany(auth()->user()->company_id);
        $lookups = Lookup::where('is_active', true)->get()->groupBy('type');
        return view('quotations.create', compact('customers', 'products', 'company', 'lookups'));
    }

    /**
     * Live price preview. Products are always resolved inside the user's company.
     */
    public function calculate(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.design_id' => 'nullable|integer',
            'items.*.sizes' => 'nullable|array',
        ]);

        $calculationService = app(QuotationCalculationService::class);
        $totals = $calculationService->calculateQuotationData($request->all());

        return response()->json($totals);
    }

    public function store(QuotationRequest $request)
    {
        $validated = $request->validated();
        $idempKey = $validated['idempotency_key'] ?? null;

        if ($idempKey) {
            // Atomic reservation for 60 seconds
            if (!Cache::add('quote_idemp_lock_' . $idempKey, true, 60)) {
                $existingQuoteId = Cache::get('quote_idemp_val_' . $idempKey);
                if ($existingQuoteId) {
                    if ($request->wantsJson()) {
                        return response()->json(['id' => $existingQuoteId]);
                    }
                    return redirect()->route('quotations.show', $existingQuoteId)
                        ->with('success', 'Quotation draft created and PDF generated successfully.');
                }
                abort(409, 'Duplicate request is currently processing.');
            }
        }

        try {
            $quotation = $this->quotationService->saveDraft($validated, $request->user()->company_id);
        } catch (ValidationException $e) {
            $this->releaseIdempotency($idempKey);
            throw $e;
        }

        try {
            $this->quotationService->generateAndAttachPDF($quotation);
        } catch (\Throwable $e) {
            // Keep the database and the PDF consistent: a quotation without its PDF is removed.
            Log::error('PDF generation failed while saving quotation', ['quotation_id' => $quotation->id, 'error' => $e->getMessage()]);
            $this->deleteQuotation($quotation);
            $this->releaseIdempotency($idempKey);

            $message = 'The PDF could not be generated, so the quotation was not saved. Please try again.';
            return $request->wantsJson()
                ? response()->json(['message' => $message], 500)
                : back()->withInput()->withErrors(['pdf' => $message]);
        }

        if ($idempKey) {
            Cache::put('quote_idemp_val_' . $idempKey, $quotation->id, now()->addMinutes(15));
        }

        if ($request->wantsJson()) {
            return response()->json(['id' => $quotation->id]);
        }

        return redirect()->route('quotations.show', $quotation->id)->with('success', 'Quotation draft created and PDF generated successfully.');
    }

    public function edit(Quotation $quotation)
    {
        if ($quotation->status === \App\Constants\QuotationStatus::APPROVED) {
            return redirect()->route('quotations.show', $quotation->id)->with('error', 'Approved quotations cannot be edited.');
        }

        $this->authorize('update', $quotation);

        $quotation->load(['items.sizes', 'customer']);

        $customers = Customer::where('status', 'Active')->get();
        $products = Product::where('status', 'Active')->get();

        $formattedDate = $quotation->quotation_date
            ? $quotation->quotation_date->format('Y-m-d')
            : now()->format('Y-m-d');

        $quoteData = [
            'id' => $quotation->id,
            'customer_id' => $quotation->customer_id,
            'project_name' => $quotation->project_name,
            'client_name' => $quotation->client_name,
            'quote_no' => $quotation->quotation_number,
            'date' => $formattedDate,
            'items' => $quotation->items->map(function ($item) {
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
                    'glass_type' => $item->glass_type ?? '',
                    'glass_thickness' => $item->glass_thickness ?? '',
                    'mesh_type' => $item->mesh_type ?? '',
                    'profile_color' => $item->profile_color ?? $item->color ?? '',
                    'hardware_color' => $item->hardware_color ?? '',
                    'hardware_brand' => $item->hardware_brand ?? '',
                    'handle_type' => $item->handle_type ?? '',
                    'remarks' => $item->notes ?? '',
                    'profile_details' => $item->profile_details ?? [],
                    'accessories_details' => $item->accessories_details ?? [],
                    'sizes' => $item->sizes->map(function ($size) use ($item) {
                        return [
                            'id' => $size->id,
                            'width' => (float) $size->width,
                            'height' => (float) $size->height,
                            'qty' => (int) $size->quantity,
                            'unit' => $size->unit ?? $item->unit ?? 'mm',
                        ];
                    })->toArray(),
                ];
            })->toArray(),
        ];

        return view('quotations.edit', compact('quotation', 'customers', 'products', 'quoteData'));
    }

    public function update(QuotationRequest $request, Quotation $quotation)
    {
        if ($quotation->status === \App\Constants\QuotationStatus::APPROVED) {
            return $request->wantsJson()
                ? response()->json(['message' => 'Approved quotations cannot be edited.'], 422)
                : redirect()->route('quotations.show', $quotation->id)->with('error', 'Approved quotations cannot be edited.');
        }

        $this->authorize('update', $quotation);

        $validated = $request->validated();

        $quotation = $this->quotationService->updateQuotation($quotation, $validated);

        try {
            $this->quotationService->generateAndAttachPDF($quotation);
        } catch (\Throwable $e) {
            Log::error('PDF regeneration failed while updating quotation', ['quotation_id' => $quotation->id, 'error' => $e->getMessage()]);
            $message = 'The quotation was saved, but the PDF could not be regenerated. Open the quotation and try again.';
            return $request->wantsJson()
                ? response()->json(['id' => $quotation->id, 'message' => $message], 500)
                : redirect()->route('quotations.show', $quotation->id)->with('error', $message);
        }

        if ($request->wantsJson()) {
            return response()->json(['id' => $quotation->id]);
        }

        return redirect()->route('quotations.show', $quotation->id)->with('success', 'Quotation updated and PDF regenerated successfully.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load(['items.sizes', 'customer', 'companySetting']);

        $validationResult = $this->validationService->validate($quotation);
        $shareLinks = $this->shareLinks($quotation);

        return view('quotations.show', compact('quotation', 'validationResult', 'shareLinks'));
    }

    /**
     * Public PDF for the client. Reached only through a signed link (see shareLinks()).
     * The tenant scope is bypassed on purpose: the signature pins this exact quotation id.
     */
    public function sharedPdf(int $quotation)
    {
        $model = Quotation::withoutGlobalScopes()
            ->with(['items.sizes', 'customer', 'companySetting'])
            ->findOrFail($quotation);

        $path = $this->pdfService->getPhysicalPath($model);
        $safeName = preg_replace('/[^A-Za-z0-9\-_]/', '_', $model->quotation_number ?: 'Quotation');

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $safeName . '.pdf"',
        ]);
    }

    /**
     * Links the user can send to the client: a 30-day signed PDF link, a WhatsApp
     * message and an e-mail draft. Nothing is sent automatically.
     */
    private function shareLinks(Quotation $quotation): array
    {
        $link = URL::temporarySignedRoute('quotations.shared', now()->addDays(30), ['quotation' => $quotation->id]);

        $number = $quotation->quotation_number;
        $project = $quotation->project_name ?: 'your project';
        $greeting = $quotation->client_name ? 'Dear ' . $quotation->client_name . ', ' : '';
        $message = $greeting . 'please find quotation ' . $number . ' for ' . $project . ': ' . $link;

        $customer = $quotation->customer;
        $phone = preg_replace('/\D+/', '', (string) optional($customer)->phone);
        if (strlen($phone) === 10) {
            $phone = '91' . $phone;
        }

        $email = (string) optional($customer)->email;
        $subject = 'Quotation ' . $number . ' - ' . $project;

        return [
            'link' => $link,
            'whatsapp' => 'https://wa.me/' . $phone . '?text=' . rawurlencode($message),
            'mailto' => 'mailto:' . rawurlencode($email) . '?subject=' . rawurlencode($subject) . '&body=' . rawurlencode($message),
        ];
    }

    public function destroy(Quotation $quotation)
    {
        $this->authorize('delete', $quotation);

        $this->deleteQuotation($quotation);

        return redirect()->route('quotations.index')->with('success', 'Draft quotation deleted.');
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

        $outcome = DB::transaction(function () use ($quotation) {
            $locked = Quotation::query()->whereKey($quotation->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== \App\Constants\QuotationStatus::DRAFT) {
                return ['approved' => false, 'message' => "Only draft quotations can be approved. Current status: {$locked->status}."];
            }

            $locked->load(['items.sizes', 'customer', 'companySetting']);
            $validationResult = $this->validationService->validate($locked);

            if (!$validationResult->canApprove) {
                $failedMsgs = implode(', ', array_map(fn ($f) => $f->label, $validationResult->failedChecks));
                return [
                    'approved' => false,
                    'message' => "Quotation cannot be approved! Commercial Validation score is {$validationResult->scorePercent}%. Fix missing items: {$failedMsgs}",
                ];
            }

            $locked->update(['status' => \App\Constants\QuotationStatus::APPROVED]);

            return ['approved' => true, 'message' => null];
        });

        if (!$outcome['approved']) {
            return redirect()->back()->with('error', $outcome['message']);
        }

        return redirect()->route('quotations.show', $quotation->id)->with('success', 'Quotation validated 100% and approved.');
    }

    /**
     * Customer-facing lifecycle after approval:
     *   Approved -> Sent -> Accepted | Rejected
     * Draft quotations must be approved first. Accepted and Rejected are final.
     */
    public function updateStatus(Request $request, Quotation $quotation)
    {
        $data = $request->validate([
            'status' => ['required', \Illuminate\Validation\Rule::in([
                \App\Constants\QuotationStatus::SENT,
                \App\Constants\QuotationStatus::ACCEPTED,
                \App\Constants\QuotationStatus::REJECTED,
            ])],
        ]);

        $next = $data['status'];

        $transitions = [
            \App\Constants\QuotationStatus::APPROVED => [\App\Constants\QuotationStatus::SENT],
            \App\Constants\QuotationStatus::SENT => [\App\Constants\QuotationStatus::ACCEPTED, \App\Constants\QuotationStatus::REJECTED],
        ];

        $error = DB::transaction(function () use ($quotation, $next, $transitions) {
            $locked = Quotation::query()->whereKey($quotation->id)->lockForUpdate()->firstOrFail();

            if (!in_array($next, $transitions[$locked->status] ?? [], true)) {
                return "A quotation with status {$locked->status} cannot be marked as {$next}.";
            }

            $locked->update(['status' => $next]);

            return null;
        });

        return $error
            ? back()->with('error', $error)
            : back()->with('success', "Quotation marked as {$next}.");
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
                $material = $bomLine->material;
                $category = $material ? $material->category : 'Other';
                $cost = (float) $bomLine->total_cost;

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

                // Weight is only meaningful for profiles sold per metre
                $weightKg = 0;
                if ($material && $material->weight_per_mtr && strtolower((string) $material->uom) === 'mtr') {
                    $weightKg = (float) $material->weight_per_mtr * (float) $bomLine->calculated_qty;
                }

                $lineItems[] = [
                    'material_category' => $category,
                    'material_name' => $bomLine->material_name,
                    'sku' => $bomLine->material_sku ?? '-',
                    'unit' => $material ? $material->uom : 'nos',
                    'quantity' => $bomLine->calculated_qty,
                    'weight_kg' => $weightKg,
                    'waste_percent' => $material ? $material->waste_percent : 0,
                    'rate' => $bomLine->unit_cost,
                    'total_cost' => $bomLine->total_cost,
                    'remarks' => $bomLine->rule_type,
                ];
            }

            $w = $item->width ?? 0;
            $h = $item->height ?? 0;

            $boms[] = [
                'item_code' => $item->item_code ?? 'Item',
                'dimensions' => "{$w}x{$h}",
                'total_qty' => $item->quantity ?? 1,
                'total_area_sqft' => $item->area ?? 0,
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

    private function deleteQuotation(Quotation $quotation): void
    {
        foreach ($quotation->items()->get() as $item) {
            $item->boms()->delete();
        }
        $quotation->items()->delete();
        $quotation->delete();
    }

    private function releaseIdempotency(?string $idempKey): void
    {
        if ($idempKey) {
            Cache::forget('quote_idemp_lock_' . $idempKey);
        }
    }
}
