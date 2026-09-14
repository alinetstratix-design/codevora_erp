<?php

namespace App\Services\BOM;

use InvalidArgumentException;

class UnitConverter
{
    /**
     * Convert from one unit to another.
     * We assume mm for lengths and sqm for areas by default, but this can convert anything we define.
     */
    public static function convert(float $value, string $from, string $to): float
    {
        if (empty($from) || empty($to)) {
            return $value;
        }

        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        if ($from === $to) {
            return $value;
        }

        // Base unit for length is 'mm'. Base unit for area is 'sqmm'.
        // First convert to base unit, then to target unit.
        
        $baseValue = self::toBase($value, $from);
        return self::fromBase($baseValue, $to);
    }

    private static function toBase(float $value, string $from): float
    {
        return match ($from) {
            // Length (base: mm)
            'mm' => $value,
            'cm' => $value * 10,
            'm', 'mtr' => $value * 1000,
            'inch', 'in' => $value * 25.4,
            'ft', 'feet' => $value * 304.8,

            // Area (base: sqmm)
            'sqmm' => $value,
            'sqcm' => $value * 100,
            'sqm', 'sqmtr' => $value * 1000000,
            'sqft' => $value * 92903.04,

            // Weight (base: kg)
            'g' => $value / 1000,
            'kg' => $value,

            // Quantities
            'pcs', 'nos', 'piece' => $value,
            
            default => throw new InvalidArgumentException("Unknown unit for conversion: {$from}")
        };
    }

    private static function fromBase(float $baseValue, string $to): float
    {
        return match ($to) {
            // Length
            'mm' => $baseValue,
            'cm' => $baseValue / 10,
            'm', 'mtr' => $baseValue / 1000,
            'inch', 'in' => $baseValue / 25.4,
            'ft', 'feet' => $baseValue / 304.8,

            // Area
            'sqmm' => $baseValue,
            'sqcm' => $baseValue / 100,
            'sqm', 'sqmtr' => $baseValue / 1000000,
            'sqft' => $baseValue / 92903.04,

            // Weight
            'g' => $baseValue * 1000,
            'kg' => $baseValue,

            // Quantities
            'pcs', 'nos', 'piece' => $baseValue,
            
            default => throw new InvalidArgumentException("Unknown unit for conversion: {$to}")
        };
    }
}
