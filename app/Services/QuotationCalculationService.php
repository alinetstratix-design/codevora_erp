<?php

namespace App\Services;

use App\Models\Product;
use App\Models\CompanySetting;
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
     * Calculate Area in Sq.Ft based on width, height and their measurement unit.
     */
    public function calculateArea($width, $height, $dimensionUnit = 'mm', $areaUnit = 'sqft')
    {
        $w = (float)$width;
        $h = (float)$height;

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
        $w = (float)($sizeData['width'] ?? $sizeData['dimension_w'] ?? $sizeData['w'] ?? 0);
        $h = (float)($sizeData['height'] ?? $sizeData['dimension_h'] ?? $sizeData['h'] ?? 0);
        $unit = $sizeData['unit'] ?? 'mm';
        $qty = (int)($sizeData['quantity'] ?? $sizeData['qty'] ?? 1);
        $qty = $qty > 0 ? $qty : 1;

        $unitAreaSqFt = $this->calculateArea($w, $h, $unit, 'sqft');
        $totalAreaSqFt = $unitAreaSqFt * $qty;

        $bomCost = 0.0;
        $bomResultFull = ['items' => [], 'material_total' => 0.0];

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

            $bomCost = (float)$bomResultFull['material_total']; // This is total BOM for $qty

            // Fallback calculation using product base_rate if BOM rule total is 0
            if ($bomCost <= 0 && (float)$product->base_rate > 0) {
                $bomCost = (float)$product->base_rate * $totalAreaSqFt;
                $bomResultFull['material_total'] = $bomCost;
                $bomResultFull['has_zero_cost_materials'] = false;
            }
        } else {
            // Fallback for missing product
            $bomCost = (float)($sizeData['material_cost'] ?? 0) * $qty;
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
     * Calculate and prepare complete quotation data dynamically using Phase B BOM architecture.
     */
    public function calculateQuotationData(array $validated): array
    {
        $items = $validated['items'] ?? [];
        $discount = (float)($validated['discount'] ?? $validated['discount_amount'] ?? 0);
        $transportation = (float)($validated['transportation'] ?? $validated['freight_charges'] ?? 0);
        $installation = (float)($validated['installation'] ?? $validated['installation_cost'] ?? 0);
        
        $companyId = auth()->check() ? auth()->user()->company_id : null;
        $company = $companyId 
            ? (CompanySetting::where('company_id', $companyId)->first() ?? CompanySetting::find($companyId) ?? CompanySetting::first()) 
            : CompanySetting::first();
        $gstPercent = $company ? (float) ($company->default_tax_percent ?? 18.0) : 18.0;

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

        // Preload products for BOM
        $productIds = array_filter(array_column($items, 'product_id'));
        $designIds = array_filter(array_column($items, 'design_id'));
        
        $products = Product::withoutGlobalScopes()->with(['components' => function ($query) use ($designIds) {
            $query->withoutGlobalScopes();
            if (!empty($designIds)) {
                $query->where(function($q) use ($designIds) {
                    $q->whereIn('design_id', $designIds)->orWhereNull('design_id');
                });
            } else {
                $query->whereNull('design_id');
            }
            $query->with(['material' => function($mq) {
                $mq->withoutGlobalScopes();
            }]);
        }])->whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($items as $index => $itemData) {
            $productId = $itemData['product_id'] ?? null;
            $product = $productId ? $products->get($productId) : null;
            
            $laborRate = $product ? (float)$product->labor_rate_per_sqft : 0.0;
            $marginPercent = $product ? (float)$product->profit_margin_percent : 0.0;

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
                
                $itemBomCost += $sizeResult['bom_cost'];
                $itemLaborCost += $sizeResult['labor_cost'];
                $itemCostBasis += $sizeResult['cost_basis'];
                $itemMarginAmount += $sizeResult['margin_amount'];
                $itemLineTotal += $sizeResult['line_total'];
                $itemTotalAreaSqFt += $sizeResult['total_area'];
                $itemTotalQty += $sizeResult['quantity'];

                foreach ($sizeResult['bom_result']['items'] as $bomLine) {
                    $matId = $bomLine['material_id'];
                    $ruleType = $bomLine['rule_type'];
                    $key = $matId . '_' . $ruleType;
                    if (isset($mergedBomItems[$key])) {
                        $mergedBomItems[$key]['total_qty'] += $bomLine['total_qty'];
                        $mergedBomItems[$key]['total_cost'] += $bomLine['total_cost'];
                    } else {
                        $mergedBomItems[$key] = $bomLine;
                    }
                }

                $sizeId = $sizeInput['id'] ?? null;
                $processedSizes[] = [
                    'id' => $sizeId,
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
                'has_zero_cost_materials' => $itemHasZeroCostMaterials ?? false
            ];

            $customRate = (float)($itemData['rate_per_sqft'] ?? $itemData['value_per_sqft'] ?? $itemData['custom_rate'] ?? 0);
            $customUnitPrice = (float)($itemData['unit_price'] ?? 0);

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

            // Global accumulators
            $globalBomCostTotal += $itemBomCost;
            $globalAdditionalCostTotal += $itemLaborCost;
            $globalCostBasisTotal += $itemCostBasis;
            $globalMarginTotal += $itemMarginAmount;
            $globalLineTotal += $itemLineTotal;
            $totalAreaSqFt += $itemTotalAreaSqFt;
            $totalComponents += $itemTotalQty;
            
            if (($mergedBomResult['has_zero_cost_materials'] ?? false) === true) {
                $hasZeroCostMaterials = true;
            }

            $code = $itemData['item_code'] ?? $itemData['code'] ?? ('W' . $itemCounter);
            $prodName = !empty($itemData['product_name']) ? $itemData['product_name'] : ($product ? $product->name : ('Window ' . $code));
            $profileSystem = !empty($itemData['profile_system']) 
                ? $itemData['profile_system'] 
                : ($product ? ($product->profile_series ?? $product->series ?? $product->category ?? 'Casement Series') : 'Casement Series');
            
            $profileBrand = !empty($itemData['profile_brand']) ? $itemData['profile_brand'] : ($product ? ($product->profile_brand ?? '') : '');
            $profileSeries = !empty($itemData['profile_series']) ? $itemData['profile_series'] : ($product ? ($product->profile_series ?? $product->series ?? '') : '');
            $openingType = !empty($itemData['opening_type']) ? $itemData['opening_type'] : ($product ? ($product->opening_type ?? '') : '');
            $glassType = !empty($itemData['glass_type']) ? $itemData['glass_type'] : ($product ? ($product->glass_type ?? '') : '');
            $glassThickness = !empty($itemData['glass_thickness']) ? $itemData['glass_thickness'] : ($product ? ($product->glass_thickness ?? '') : '');
            $hardwareBrand = !empty($itemData['hardware_brand']) ? $itemData['hardware_brand'] : ($product ? ($product->hardware_brand ?? '') : '');
            $meshType = (!empty($itemData['mesh_type']) && $itemData['mesh_type'] !== 'No') ? $itemData['mesh_type'] : ($product ? ($product->mesh_type ?? 'No') : 'No');

            $profileDetails = !empty($itemData['profile_details']) ? $itemData['profile_details'] : ($product && is_array($product->profile_details) ? $product->profile_details : []);
            if (isset($itemData['profile_color']) && !empty($itemData['profile_color'])) {
                $profileDetails['Profile Color'] = $itemData['profile_color'];
            }
            $colorVal = $itemData['color'] ?? $itemData['profile_color'] ?? ($profileDetails['Profile Color'] ?? 'WHITE');
            
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
                'handle_type' => $itemData['handle_type'] ?? 'C-Type Handle',
                'hardware_color' => $itemData['hardware_color'] ?? 'WHITE',
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

        $subtotal = $globalLineTotal;
        $taxableAmount = max(0, $subtotal - $discount + $transportation + $installation);
        $taxAmount = $taxableAmount * ($gstPercent / 100);
        $grandTotal = $taxableAmount + $taxAmount;

        $totalAreaRounded = round($totalAreaSqFt, 2);
        $avgPriceExGst = $totalAreaRounded > 0 ? round($subtotal / $totalAreaRounded, 2) : 0.00;
        $avgPriceIncGst = $totalAreaRounded > 0 ? round($grandTotal / $totalAreaRounded, 2) : 0.00;

        $quoteNo = $validated['quote_no'] ?? $validated['quotation_number'] ?? ('QT-' . date('Ymd') . '-' . substr(uniqid(), -4));

        return [
            'quotation_data' => [
                'quotation_number' => $quoteNo,
                'quotation_date' => $validated['date'] ?? $validated['quotation_date'] ?? date('Y-m-d'),
                'valid_until' => $validated['valid_till'] ?? $validated['valid_until'] ?? date('Y-m-d', strtotime('+30 days')),
                'customer_id' => $validated['customer_id'] ?? null,
                'company_setting_id' => $company ? $company->id : null,
                'company_id' => auth()->user() ? auth()->user()->company_id : ($company ? $company->company_id : null),
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
                'basic_value' => round($subtotal, 2),
                'subtotal' => round($subtotal, 2),
                'discount' => round($discount, 2),
                'discount_percent' => $validated['discount_percent'] ?? 0,
                'transportation' => round($transportation, 2),
                'installation' => round($installation, 2),
                'taxable_amount' => round($taxableAmount, 2),
                'tax_percent' => $gstPercent,
                'tax_amount' => round($taxAmount, 2),
                'gst' => round($taxAmount, 2),
                'gst_percent' => $gstPercent,
                'total_project_cost' => round($subtotal, 2),
                'grand_total' => round($grandTotal, 2),
                'amount_in_words' => $this->amountInWords($grandTotal),
                'avg_price_sqft_ex_gst' => $avgPriceExGst,
                'avg_price_sqft_inc_gst' => $avgPriceIncGst,
                'status' => $validated['status'] ?? 'Draft',
                'terms_conditions' => $validated['terms_conditions'] ?? ($company->terms_conditions ?? null),
                'bank_details' => $validated['bank_details'] ?? ($company->bank_details ?? null),
            ],
            'items' => $processedItems,
            'has_zero_cost_materials' => $hasZeroCostMaterials
        ];
    }
    /**
     * Convert numeric amount to words (Indian Rupees)
     */
    public function amountInWords(float $number)
    {
        $no = floor($number);
        $point = round($number - $no, 2) * 100;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '', '1' => 'One', '2' => 'Two',
            '3' => 'Three', '4' => 'Four', '5' => 'Five', '6' => 'Six',
            '7' => 'Seven', '8' => 'Eight', '9' => 'Nine',
            '10' => 'Ten', '11' => 'Eleven', '12' => 'Twelve',
            '13' => 'Thirteen', '14' => 'Fourteen',
            '15' => 'Fifteen', '16' => 'Sixteen', '17' => 'Seventeen',
            '18' => 'Eighteen', '19' => 'Nineteen', '20' => 'Twenty',
            '30' => 'Thirty', '40' => 'Forty', '50' => 'Fifty',
            '60' => 'Sixty', '70' => 'Seventy',
            '80' => 'Eighty', '90' => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $counter = count($str);
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $plural = ($counter && $number > 9) ? 's' : null;
                $str[] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                    : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
            } else {
                $str[] = null;
            }
        }
        $str = array_reverse($str);
        $result = trim(implode('', $str));
        $points = ($point > 0) ? " and " . ($words[floor($point / 10) * 10] ?? '') . " " . ($words[$point % 10] ?? '') . " Paise" : '';

        return ($result ? $result : 'Zero') . " Rupees " . $points . " Only";
    }
}
