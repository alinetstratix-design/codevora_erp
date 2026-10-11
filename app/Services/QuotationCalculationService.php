<?php

namespace App\Services;

use App\Models\CompanySetting;
use App\Models\Product;
use App\Services\BOM\BomEngine;
use App\Services\BOM\CostingEngine;

class QuotationCalculationService
{
    protected BomEngine $bomEngine;
    protected CostingEngine $costingEngine;

    public function __construct(BomEngine $bomEngine, CostingEngine $costingEngine)
    {
        $this->bomEngine = $bomEngine;
        $this->costingEngine = $costingEngine;
    }

    /**
     * Calculate area in Sq.Ft based on width, height and their measurement unit.
     */
    public function calculateArea($width, $height, $dimensionUnit = 'mm', $areaUnit = 'sqft')
    {
        $w = (float) $width;
        $h = (float) $height;

        if ($w <= 0 || $h <= 0) {
            return 0.000;
        }

        $unit = strtolower(trim($dimensionUnit ?? 'mm'));

        switch ($unit) {
            case 'cm':
                $areaSqFt = ($w * $h) / 929.0304;
                break;
            case 'inch':
            case 'in':
                $areaSqFt = ($w * $h) / 144.0;
                break;
            case 'ft':
                $areaSqFt = $w * $h;
                break;
            case 'm':
            case 'meter':
                $areaSqFt = ($w * $h) * 10.7639;
                break;
            case 'mm':
            default:
                // 1 Sq.Ft. = 92,903.04 mm²
                $areaSqFt = ($w * $h) / 92903.04;
                break;
        }

        return round($areaSqFt, 3);
    }

    public function calculateSize(array $sizeData, ?Product $product, float $laborRate, float $marginPercent, array $itemData = []): array
    {
        $w = (float) ($sizeData['width'] ?? $sizeData['dimension_w'] ?? $sizeData['w'] ?? 0);
        $h = (float) ($sizeData['height'] ?? $sizeData['dimension_h'] ?? $sizeData['h'] ?? 0);
        $unit = $sizeData['unit'] ?? 'mm';
        $qty = (int) ($sizeData['quantity'] ?? $sizeData['qty'] ?? 1);
        $qty = $qty > 0 ? $qty : 1;

        $unitAreaSqFt = $this->calculateArea($w, $h, $unit, 'sqft');
        $totalAreaSqFt = $unitAreaSqFt * $qty;

        $bomCost = 0.0;
        $bomResultFull = ['items' => [], 'material_total' => 0.0, 'has_zero_cost_materials' => false, 'errors' => []];

        if ($product) {
            $context = [
                'width' => $w,
                'height' => $h,
                'quantity' => $qty,
                'unit' => $unit,
                'design_id' => $itemData['design_id'] ?? null,
                'design_name' => $itemData['design_name'] ?? null,
                'glass_type' => $itemData['glass_type'] ?? null,
                'mesh_type' => $itemData['mesh_type'] ?? null,
                'profile_color' => $itemData['profile_color'] ?? null,
                'hardware_color' => $itemData['hardware_color'] ?? null,
            ];
            $bomResultRaw = $this->bomEngine->calculate($product, $context);
            $bomResultFull = $this->costingEngine->calculate($bomResultRaw);

            $bomCost = (float) $bomResultFull['material_total'];

            // Fallback to product base_rate only when the product has no BOM at all
            if ($bomCost <= 0 && (float) $product->base_rate > 0) {
                $bomCost = (float) $product->base_rate * $totalAreaSqFt;
                $bomResultFull['material_total'] = $bomCost;
                $bomResultFull['has_zero_cost_materials'] = false;
            }
        } else {
            $bomCost = (float) ($sizeData['material_cost'] ?? 0) * $qty;
        }

        $totalLaborCost = $totalAreaSqFt * $laborRate;
        $costBasis = $bomCost + $totalLaborCost;
        $marginAmount = $costBasis * ($marginPercent / 100);
        $lineTotal = $costBasis + $marginAmount;

        return [
            'width' => $w,
            'height' => $h,
            'unit' => $unit,
            'quantity' => $qty,
            'unit_area' => $unitAreaSqFt,
            'total_area' => $totalAreaSqFt,
            'bom_cost' => $bomCost,
            'labor_cost' => $totalLaborCost,
            'cost_basis' => $costBasis,
            'margin_amount' => $marginAmount,
            'line_total' => $lineTotal,
            'bom_result' => $bomResultFull,
        ];
    }

    /**
     * Calculate complete quotation data using the BOM architecture.
     * Products are always resolved inside the current user's company.
     */
    public function calculateQuotationData(array $validated): array
    {
        $items = $validated['items'] ?? [];

        $companyId = auth()->user()?->company_id ?? session('company_id');
        $company = CompanySetting::forCompany($companyId);

        $processedItems = [];
        $itemCounter = 1;

        $globalBomCostTotal = 0.0;
        $globalAdditionalCostTotal = 0.0;
        $globalCostBasisTotal = 0.0;
        $globalMarginTotal = 0.0;
        $globalLineTotal = 0.0;
        $totalAreaSqFt = 0.0;
        $totalComponents = 0;
        $hasZeroCostMaterials = false;
        $bomErrors = [];

        // Preload products for BOM (scoped to the user's company by the model's global scope)
        $productIds = array_values(array_filter(array_column($items, 'product_id')));
        $designIds = array_values(array_filter(array_column($items, 'design_id')));

        $products = Product::with(['components' => function ($query) use ($designIds) {
            if (!empty($designIds)) {
                $query->where(function ($q) use ($designIds) {
                    $q->whereIn('design_id', $designIds)->orWhereNull('design_id');
                });
            } else {
                $query->whereNull('design_id');
            }
            $query->with('material');
        }])->whereIn('id', $productIds)->get()->keyBy('id');

        // Reject products or designs that do not belong to the current company.
        // Never fall back to client-supplied costs for a product we cannot verify.
        $unknownProducts = array_diff(array_map('intval', $productIds), array_map('intval', $products->keys()->all()));
        $validDesignIds = empty($designIds) ? [] : \App\Models\Design::whereIn('id', $designIds)->pluck('id')->map(fn ($id) => (int) $id)->all();
        $unknownDesigns = array_diff(array_map('intval', $designIds), $validDesignIds);

        if (!empty($unknownProducts) || !empty($unknownDesigns)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'items' => ['One or more selected products or designs are not available for your company. Please reselect them.'],
            ]);
        }

        foreach ($items as $itemData) {
            $productId = $itemData['product_id'] ?? null;
            $product = $productId ? $products->get($productId) : null;

            $laborRate = $product ? (float) $product->labor_rate_per_sqft : 0.0;
            $marginPercent = $product ? (float) $product->profit_margin_percent : 0.0;

            $sizesInput = $itemData['sizes'] ?? [];
            if (empty($sizesInput)) {
                $sizesInput = [$itemData];
            }

            $itemBomCost = 0.0;
            $itemLaborCost = 0.0;
            $itemCostBasis = 0.0;
            $itemMarginAmount = 0.0;
            $itemLineTotal = 0.0;
            $itemTotalAreaSqFt = 0.0;
            $itemTotalQty = 0;
            $itemHasZeroCostMaterials = false;

            $mergedBomItems = [];
            $processedSizes = [];

            foreach ($sizesInput as $sizeInput) {
                $sizeResult = $this->calculateSize($sizeInput, $product, $laborRate, $marginPercent, $itemData);

                if ($sizeResult['bom_result']['has_zero_cost_materials'] ?? false) {
                    $itemHasZeroCostMaterials = true;
                }

                foreach ($sizeResult['bom_result']['errors'] ?? [] as $error) {
                    $bomErrors[] = $error;
                }

                $itemBomCost += $sizeResult['bom_cost'];
                $itemLaborCost += $sizeResult['labor_cost'];
                $itemCostBasis += $sizeResult['cost_basis'];
                $itemMarginAmount += $sizeResult['margin_amount'];
                $itemLineTotal += $sizeResult['line_total'];
                $itemTotalAreaSqFt += $sizeResult['total_area'];
                $itemTotalQty += $sizeResult['quantity'];

                foreach ($sizeResult['bom_result']['items'] as $bomLine) {
                    $key = $bomLine['material_id'] . '_' . $bomLine['rule_type'];
                    if (isset($mergedBomItems[$key])) {
                        $mergedBomItems[$key]['total_qty'] += $bomLine['total_qty'];
                        $mergedBomItems[$key]['total_cost'] += $bomLine['total_cost'];
                    } else {
                        $mergedBomItems[$key] = $bomLine;
                    }
                }

                $processedSizes[] = [
                    'id' => $sizeInput['id'] ?? null,
                    'width' => $sizeResult['width'],
                    'height' => $sizeResult['height'],
                    'unit' => $sizeResult['unit'],
                    'quantity' => $sizeResult['quantity'],
                    'area' => round($sizeResult['total_area'], 3),
                    'amount' => round($sizeResult['line_total'], 2),
                ];
            }

            $mergedBomResult = [
                'items' => array_values($mergedBomItems),
                'material_total' => $itemBomCost,
                'has_zero_cost_materials' => $itemHasZeroCostMaterials,
            ];

            $customRate = (float) ($itemData['rate_per_sqft'] ?? $itemData['value_per_sqft'] ?? $itemData['custom_rate'] ?? 0);
            $customUnitPrice = (float) ($itemData['unit_price'] ?? 0);

            if ($customRate > 0) {
                $itemLineTotal = round($customRate * $itemTotalAreaSqFt, 2);
                $unitPrice = $itemTotalQty > 0 ? $itemLineTotal / $itemTotalQty : $itemLineTotal;
                $valuePerSqFt = $customRate;
            } elseif ($customUnitPrice > 0) {
                $itemLineTotal = round($customUnitPrice * $itemTotalQty, 2);
                $unitPrice = $customUnitPrice;
                $valuePerSqFt = $itemTotalAreaSqFt > 0 ? $itemLineTotal / $itemTotalAreaSqFt : 0.00;
            } else {
                $unitPrice = $itemTotalQty > 0 ? $itemLineTotal / $itemTotalQty : $itemLineTotal;
                $valuePerSqFt = $itemTotalAreaSqFt > 0 ? $itemLineTotal / $itemTotalAreaSqFt : 0.00;
            }

            // Margin always reconciles with the final line total (custom rate may override the computed price)
            $itemMarginAmount = $itemLineTotal - $itemCostBasis;

            $globalBomCostTotal += $itemBomCost;
            $globalAdditionalCostTotal += $itemLaborCost;
            $globalCostBasisTotal += $itemCostBasis;
            $globalMarginTotal += $itemMarginAmount;
            $globalLineTotal += $itemLineTotal;
            $totalAreaSqFt += $itemTotalAreaSqFt;
            $totalComponents += $itemTotalQty;

            if ($itemHasZeroCostMaterials) {
                $hasZeroCostMaterials = true;
            }

            $code = $itemData['item_code'] ?? $itemData['code'] ?? ('W' . $itemCounter);
            $prodName = !empty($itemData['product_name']) ? $itemData['product_name'] : ($product ? $product->name : ('Window ' . $code));
            $profileSystem = !empty($itemData['profile_system'])
                ? $itemData['profile_system']
                : ($product ? ($product->profile_series ?? $product->category ?? '') : '');

            $profileBrand = !empty($itemData['profile_brand']) ? $itemData['profile_brand'] : ($product ? ($product->profile_brand ?? '') : '');
            $profileSeries = !empty($itemData['profile_series']) ? $itemData['profile_series'] : ($product ? ($product->profile_series ?? '') : '');
            $openingType = !empty($itemData['opening_type']) ? $itemData['opening_type'] : ($product ? ($product->opening_type ?? '') : '');
            $glassType = !empty($itemData['glass_type']) ? $itemData['glass_type'] : ($product ? ($product->glass_type ?? '') : '');
            $glassThickness = !empty($itemData['glass_thickness']) ? $itemData['glass_thickness'] : ($product ? ($product->glass_thickness ?? '') : '');
            $hardwareBrand = !empty($itemData['hardware_brand']) ? $itemData['hardware_brand'] : ($product ? ($product->hardware_brand ?? '') : '');
            $meshType = (!empty($itemData['mesh_type']) && $itemData['mesh_type'] !== 'No') ? $itemData['mesh_type'] : ($product ? ($product->mesh_type ?? 'No') : 'No');

            $profileDetails = !empty($itemData['profile_details']) ? $itemData['profile_details'] : ($product && is_array($product->profile_details) ? $product->profile_details : []);
            if (!empty($itemData['profile_color'])) {
                $profileDetails['Profile Color'] = $itemData['profile_color'];
            }
            $colorVal = $itemData['color'] ?? $itemData['profile_color'] ?? ($profileDetails['Profile Color'] ?? '');

            $accessoriesDetails = !empty($itemData['accessories_details']) ? $itemData['accessories_details'] : ($product && is_array($product->accessories_details) ? $product->accessories_details : []);
            $firstSize = $processedSizes[0] ?? ['width' => 0, 'height' => 0, 'unit' => 'mm'];

            $processedItems[] = [
                'id' => $itemData['id'] ?? null,
                'product_id' => $productId,
                'design_id' => $itemData['design_id'] ?? null,
                'item_code' => $code,
                'position' => $itemData['position'] ?? ('W' . $itemCounter),
                'product_name' => $prodName,
                'system_name' => $itemData['system_name'] ?? $prodName,
                'profile_system' => $profileSystem,
                'profile_brand' => $profileBrand,
                'profile_series' => $profileSeries,
                'opening_type' => $openingType,
                'glass_type' => $glassType,
                'glass_thickness' => $glassThickness,
                'hardware_brand' => $hardwareBrand,
                'mesh_type' => $meshType,
                'color' => $colorVal,
                'profile_color' => $colorVal,
                'handle_type' => $itemData['handle_type'] ?? '',
                'hardware_color' => $itemData['hardware_color'] ?? '',
                'width' => $firstSize['width'],
                'height' => $firstSize['height'],
                'unit' => $firstSize['unit'],
                'quantity' => $itemTotalQty,
                'area' => round($itemTotalAreaSqFt, 3),
                'bom_cost' => round($itemBomCost, 2),
                'additional_cost' => round($itemLaborCost, 2),
                'cost_basis' => round($itemCostBasis, 2),
                'margin_percent' => round($marginPercent, 2),
                'margin_amount' => round($itemMarginAmount, 2),
                'line_total' => round($itemLineTotal, 2),
                'unit_price' => round($unitPrice, 2),
                'value_per_sqft' => round($valuePerSqFt, 2),
                'rate_per_sqft' => round($valuePerSqFt, 2),
                'amount' => round($itemLineTotal, 2),
                'notes' => $itemData['notes'] ?? $itemData['remarks'] ?? '',
                'profile_details' => $profileDetails,
                'accessories_details' => $accessoriesDetails,
                'drawing_metadata' => $itemData['drawing_metadata'] ?? [],
                'sort_order' => $itemCounter,
                'bom_result' => $mergedBomResult,
                'sizes' => $processedSizes,
            ];

            $itemCounter++;
        }

        // ----- Commercial totals -----
        $subtotal = round($globalLineTotal, 2);

        // Discount: percent of subtotal takes priority over a flat amount, and never exceeds the subtotal
        $discountPercent = (float) ($validated['discount_percent'] ?? 0);
        if ($discountPercent > 0) {
            $discount = round($subtotal * min($discountPercent, 100) / 100, 2);
        } else {
            $discount = min((float) ($validated['discount'] ?? 0), $subtotal);
        }
        $discount = round(max(0, $discount), 2);

        $transportation = (float) ($validated['transportation'] ?? $validated['freight_charges_rate'] ?? $validated['freight_charges'] ?? 0);
        $installation = (float) ($validated['installation'] ?? $validated['installation_cost_rate'] ?? $validated['installation_cost'] ?? 0);

        $gstPercent = isset($validated['tax_percent']) && is_numeric($validated['tax_percent'])
            ? (float) $validated['tax_percent']
            : (float) ($company->default_tax_percent ?? 18);

        $taxableAmount = max(0, $subtotal - $discount + $transportation + $installation);
        $taxAmount = round($taxableAmount * ($gstPercent / 100), 2);
        $grandTotal = round($taxableAmount + $taxAmount, 2);

        $totalAreaRounded = round($totalAreaSqFt, 2);
        $avgPriceExGst = $totalAreaRounded > 0 ? round($subtotal / $totalAreaRounded, 2) : 0.00;
        $avgPriceIncGst = $totalAreaRounded > 0 ? round($grandTotal / $totalAreaRounded, 2) : 0.00;

        $quoteNo = $validated['quote_no'] ?? $validated['quotation_number'] ?? ('QT-' . now()->format('Ymd') . '-' . substr(uniqid(), -4));
        $today = now()->toDateString();

        return [
            'quotation_data' => [
                'quotation_number' => $quoteNo,
                'quotation_date' => $validated['date'] ?? $validated['quotation_date'] ?? $today,
                'valid_until' => $validated['valid_till'] ?? $validated['valid_until'] ?? now()->addDays(30)->toDateString(),
                'customer_id' => $validated['customer_id'] ?? null,
                'company_setting_id' => $company ? $company->id : null,
                'company_id' => $companyId,
                'client_name' => $validated['client_name'] ?? null,
                'project_name' => $validated['project_name'] ?? null,
                'project_location' => $validated['project_location'] ?? $validated['address'] ?? null,
                'address' => $validated['address'] ?? $validated['project_location'] ?? null,
                'sales_person' => $validated['sales_person'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'no_of_components' => $totalComponents,
                'total_area_sqft' => $totalAreaRounded,
                'bom_cost_total' => round($globalBomCostTotal, 2),
                'additional_cost_total' => round($globalAdditionalCostTotal, 2),
                'cost_basis_total' => round($globalCostBasisTotal, 2),
                'margin_total' => round($globalMarginTotal, 2),
                'basic_value' => $subtotal,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'discount_percent' => round($discountPercent, 2),
                'transportation' => round($transportation, 2),
                'installation' => round($installation, 2),
                'taxable_amount' => round($taxableAmount, 2),
                'tax_percent' => $gstPercent,
                'tax_amount' => $taxAmount,
                'gst' => $taxAmount,
                'gst_percent' => $gstPercent,
                'total_project_cost' => $subtotal,
                'grand_total' => $grandTotal,
                'amount_in_words' => $this->amountInWords($grandTotal),
                'avg_price_sqft_ex_gst' => $avgPriceExGst,
                'avg_price_sqft_inc_gst' => $avgPriceIncGst,
                'status' => $validated['status'] ?? 'Draft',
                'terms_conditions' => $validated['terms_conditions'] ?? ($company->terms_conditions ?? null),
                'bank_details' => $validated['bank_details'] ?? ($company->bank_details ?? null),
            ],
            'items' => $processedItems,
            'has_zero_cost_materials' => $hasZeroCostMaterials,
            'bom_errors' => array_values(array_unique($bomErrors)),
        ];
    }

    /**
     * Convert an amount to Indian-English words, e.g. 123456.5 =>
     * "One Lakh Twenty Three Thousand Four Hundred and Fifty Six Rupees and Fifty Paise Only".
     */
    public function amountInWords(float $number): string
    {
        $total = (int) round(abs($number) * 100);
        $rupees = intdiv($total, 100);
        $paise = $total % 100;

        $result = ($rupees === 0 ? 'Zero' : self::indianWords($rupees))
            . ($rupees === 1 ? ' Rupee' : ' Rupees');

        if ($paise > 0) {
            $result .= ' and ' . self::twoDigitWords($paise) . ' Paise';
        }

        return $result . ' Only';
    }

    private static function indianWords(int $n): string
    {
        $crore = intdiv($n, 10000000);
        $n %= 10000000;
        $lakh = intdiv($n, 100000);
        $n %= 100000;
        $thousand = intdiv($n, 1000);
        $n %= 1000;
        $hundred = intdiv($n, 100);
        $n %= 100;

        $parts = [];

        if ($crore > 0) {
            $parts[] = self::indianWords($crore) . ' Crore';
        }
        if ($lakh > 0) {
            $parts[] = self::twoDigitWords($lakh) . ' Lakh';
        }
        if ($thousand > 0) {
            $parts[] = self::twoDigitWords($thousand) . ' Thousand';
        }
        if ($hundred > 0) {
            $parts[] = self::twoDigitWords($hundred) . ' Hundred';
        }
        if ($n > 0) {
            $tail = self::twoDigitWords($n);
            $parts[] = empty($parts) ? $tail : 'and ' . $tail;
        }

        return implode(' ', $parts);
    }

    private static function twoDigitWords(int $n): string
    {
        $ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten',
            'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        $tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        if ($n < 20) {
            return $ones[$n];
        }

        $tail = $n % 10;
        return $tens[intdiv($n, 10)] . ($tail > 0 ? ' ' . $ones[$tail] : '');
    }
}
