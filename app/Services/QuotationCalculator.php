<?php

namespace App\Services;

class QuotationCalculator
{
    /**
     * Calculate Area in Sq.Ft based on width, height and their measurement unit.
     * Supported dimension units: mm, cm, inch/in, ft, meter/m.
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

    /**
     * Calculate Weight in KG based on Area in Sq.Ft and profile multiplier.
     */
    public function calculateWeight($areaSqFt, $profileFactor = 2.162)
    {
        return round(((float)$areaSqFt) * ((float)$profileFactor), 3);
    }

    /**
     * Calculate Item Unit Price and Value per Sq.Ft.
     */
    public function calculateItemPricing($areaSqFt, $rate, $qty = 1)
    {
        $area = (float)$areaSqFt;
        $r = (float)$rate;
        $q = (int)$qty;

        $unitPrice = round($area * $r, 2);
        $valuePerSqFt = $area > 0 ? round($unitPrice / $area, 2) : 0.00;
        $totalValue = round($unitPrice * $q, 2);

        return [
            'unit_price' => $unitPrice,
            'value_per_sqft' => $valuePerSqFt,
            'amount' => $totalValue
        ];
    }

    public function calculateTotals(array $items, $discount = 0, $transportation = 0, $installation = 0, $gstPercent = 18)
    {
        $subtotal = 0;
        $totalAreaSqFt = 0;
        $totalComponents = 0;
        $processedItems = [];

        foreach ($items as $item) {
            $itemAmount = 0;
            $itemArea = 0;
            $itemWeight = 0;
            $itemQty = 0;
            $processedSizes = [];
            $rate = (float)($item['rate'] ?? 0);

            if (isset($item['sizes']) && is_array($item['sizes']) && count($item['sizes']) > 0) {
                foreach ($item['sizes'] as $size) {
                    $w = (float)($size['width'] ?? 0);
                    $h = (float)($size['height'] ?? 0);
                    $unit = $size['unit'] ?? 'mm';
                    $qty = (int)($size['quantity'] ?? 1);

                    $area = $this->calculateArea($w, $h, $unit, 'sqft');
                    $pricing = $this->calculateItemPricing($area, $rate, $qty);
                    $weight = $this->calculateWeight($area * $qty);

                    $size['area'] = $area;
                    $size['unit_price'] = $pricing['unit_price'];
                    $size['value_per_sqft'] = $pricing['value_per_sqft'];
                    $size['amount'] = $pricing['amount'];
                    $size['weight_kg'] = $weight;

                    $itemAmount += $pricing['amount'];
                    $itemArea += ($area * $qty);
                    $itemWeight += $weight;
                    $itemQty += $qty;
                    $processedSizes[] = $size;
                }
            } else {
                // Fallback for direct item inputs
                $w = (float)($item['dimension_w'] ?? $item['width'] ?? $item['w'] ?? 0);
                $h = (float)($item['dimension_h'] ?? $item['height'] ?? $item['h'] ?? 0);
                $unit = $item['unit'] ?? 'mm';
                $qty = (int)($item['qty'] ?? $item['quantity'] ?? 1);

                $area = $this->calculateArea($w, $h, $unit, 'sqft');
                $pricing = $this->calculateItemPricing($area, $rate, $qty);
                $weight = $this->calculateWeight($area * $qty);

                $itemAmount = $pricing['amount'];
                $itemArea = $area * $qty;
                $itemWeight = $weight;
                $itemQty = $qty;
            }

            $item['sizes'] = $processedSizes;
            $item['qty'] = $itemQty > 0 ? $itemQty : 1;
            $item['area'] = round($itemArea, 3);
            $item['weight_kg'] = round($itemWeight, 3);
            $item['unit_price'] = $itemArea > 0 ? round($itemAmount / $item['qty'], 2) : 0.00;
            $item['value_per_sqft'] = $itemArea > 0 ? round(($itemAmount / $item['qty']) / ($itemArea / $item['qty']), 2) : 0.00;
            $item['amount'] = round($itemAmount, 2);

            $subtotal += $itemAmount;
            $totalAreaSqFt += $itemArea;
            $totalComponents += $item['qty'];
            $processedItems[] = $item;
        }

        $taxableAmount = $subtotal - (float)$discount + (float)$transportation + (float)$installation;
        $gst = round($taxableAmount * (((float)$gstPercent) / 100), 2);
        $grandTotal = round($taxableAmount + $gst, 2);

        $totalAreaRounded = round($totalAreaSqFt, 2);
        $avgPriceExGst = $totalAreaRounded > 0 ? round($subtotal / $totalAreaRounded, 2) : 0.00;
        $avgPriceIncGst = $totalAreaRounded > 0 ? round($grandTotal / $totalAreaRounded, 2) : 0.00;

        return [
            'items' => $processedItems,
            'no_of_components' => $totalComponents,
            'total_area_sqft' => $totalAreaRounded,
            'basic_value' => round($subtotal, 2),
            'subtotal' => round($subtotal, 2),
            'discount' => round((float)$discount, 2),
            'transportation' => round((float)$transportation, 2),
            'installation' => round((float)$installation, 2),
            'gst_percent' => (float)$gstPercent,
            'gst' => $gst,
            'total_project_cost' => round($subtotal, 2),
            'grand_total' => $grandTotal,
            'avg_price_sqft_ex_gst' => $avgPriceExGst,
            'avg_price_sqft_inc_gst' => $avgPriceIncGst,
            'amount_in_words' => $this->amountInWords($grandTotal)
        ];
    }

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
