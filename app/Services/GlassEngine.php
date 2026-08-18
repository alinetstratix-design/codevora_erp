<?php

namespace App\Services;

class GlassEngine
{
    /**
     * Calculate Glass Cut Width in mm.
     */
    public function calculateGlassCutWidth(float $windowWidth, string $openingType = 'Casement Outward', int $sashCount = 1): float
    {
        $w = max(100, $windowWidth);
        $type = strtolower($openingType);

        if (str_contains($type, 'sliding')) {
            if ($sashCount >= 3) {
                return round(max(100, ($w + 60) / 3 - 90), 2);
            }
            return round(max(100, ($w + 30) / 2 - 90), 2);
        }

        if (str_contains($type, 'double') || $sashCount >= 2) {
            return round(max(100, ($w - 130) / 2), 2);
        }

        return round(max(100, $w - 110), 2);
    }

    /**
     * Calculate Glass Cut Height in mm.
     */
    public function calculateGlassCutHeight(float $windowHeight, string $openingType = 'Casement Outward', bool $hasFanCutout = false): float
    {
        $h = max(100, $windowHeight);

        if ($hasFanCutout || str_contains(strtolower($openingType), 'fan')) {
            return round(max(100, $h - 120), 2);
        }

        return round(max(100, $h - 110), 2);
    }

    /**
     * Calculate Glass Cut Area in Sq.Ft.
     */
    public function calculateGlassAreaSqFt(float $glassWidthMm, float $glassHeightMm, int $qty = 1): float
    {
        $areaSqFt = ($glassWidthMm * $glassHeightMm) / 92903.04;
        return round($areaSqFt * max(1, $qty), 3);
    }

    /**
     * Calculate Glass Weight in KG.
     * Standard glass density: 2.5 kg per mm per m² (1 m² = 10.7639 Sq.Ft.).
     */
    public function calculateGlassWeightKg(float $glassAreaSqFt, float $thicknessMm = 5.0): float
    {
        $areaSqM = $glassAreaSqFt / 10.7639;
        $weight = $areaSqM * max(1, $thicknessMm) * 2.5;
        return round($weight, 3);
    }

    /**
     * Calculate Glass Cost in INR.
     */
    public function calculateGlassCost(float $glassAreaSqFt, float $glassRatePerSqFt = 85.0): float
    {
        return round($glassAreaSqFt * max(0, $glassRatePerSqFt), 2);
    }

    /**
     * Build complete Glass Calculation DTO array.
     */
    public function calculateGlassDetails(float $windowWidth, float $windowHeight, string $openingType = 'Casement Outward', float $thicknessMm = 5.0, float $ratePerSqFt = 85.0, int $qty = 1, bool $hasFanCutout = false): array
    {
        $cutWidth = $this->calculateGlassCutWidth($windowWidth, $openingType);
        $cutHeight = $this->calculateGlassCutHeight($windowHeight, $openingType, $hasFanCutout);
        $areaSqFt = $this->calculateGlassAreaSqFt($cutWidth, $cutHeight, $qty);
        $weightKg = $this->calculateGlassWeightKg($areaSqFt, $thicknessMm);
        $cost = $this->calculateGlassCost($areaSqFt, $ratePerSqFt);

        return [
            'glass_type' => "({$qty}) {$thicknessMm}mm Clear Toughened",
            'glass_thickness' => "{$thicknessMm}mm",
            'glass_cut_width' => $cutWidth,
            'glass_cut_height' => $cutHeight,
            'glass_area_sqft' => $areaSqFt,
            'glass_weight_kg' => $weightKg,
            'glass_cost' => $cost,
            'formatted_cut_size' => "{$cutWidth} x {$cutHeight} mm",
        ];
    }
}
