<?php

namespace App\Services\BOM;

use InvalidArgumentException;

class UnitConverter
{
    /** Unit families. Conversion is only allowed inside one family. */
    private const DIMENSIONS = [
        'mm' => 'length', 'cm' => 'length', 'm' => 'length', 'mtr' => 'length',
        'inch' => 'length', 'in' => 'length', 'ft' => 'length', 'feet' => 'length',
        'sqmm' => 'area', 'sqcm' => 'area', 'sqm' => 'area', 'sqmtr' => 'area', 'sqft' => 'area',
        'g' => 'mass', 'kg' => 'mass',
        'pcs' => 'count', 'nos' => 'count', 'piece' => 'count',
    ];

    /**
     * Convert from one unit to another within the same unit family.
     * Converting between families (for example mm to pcs) is rejected.
     */
    public static function convert(float $value, string $from, string $to): float
    {
        if ($from === '' || $to === '') {
            return $value;
        }

        $from = strtolower(trim($from));
        $to = strtolower(trim($to));

        if ($from === $to) {
            return $value;
        }

        if (!isset(self::DIMENSIONS[$from])) {
            throw new InvalidArgumentException("Unknown unit for conversion: {$from}");
        }
        if (!isset(self::DIMENSIONS[$to])) {
            throw new InvalidArgumentException("Unknown unit for conversion: {$to}");
        }
        if (self::DIMENSIONS[$from] !== self::DIMENSIONS[$to]) {
            throw new InvalidArgumentException("Cannot convert {$from} to {$to}: different unit types (" . self::DIMENSIONS[$from] . " vs " . self::DIMENSIONS[$to] . ").");
        }

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

            // Mass (base: g)
            'g' => $value,
            'kg' => $value * 1000,

            // Count (base: pcs)
            'pcs', 'nos', 'piece' => $value,

            default => throw new InvalidArgumentException("Unknown unit for conversion: {$from}"),
        };
    }

    private static function fromBase(float $baseValue, string $to): float
    {
        return match ($to) {
            'mm' => $baseValue,
            'cm' => $baseValue / 10,
            'm', 'mtr' => $baseValue / 1000,
            'inch', 'in' => $baseValue / 25.4,
            'ft', 'feet' => $baseValue / 304.8,

            'sqmm' => $baseValue,
            'sqcm' => $baseValue / 100,
            'sqm', 'sqmtr' => $baseValue / 1000000,
            'sqft' => $baseValue / 92903.04,

            'g' => $baseValue,
            'kg' => $baseValue / 1000,

            'pcs', 'nos', 'piece' => $baseValue,

            default => throw new InvalidArgumentException("Unknown unit for conversion: {$to}"),
        };
    }
}
