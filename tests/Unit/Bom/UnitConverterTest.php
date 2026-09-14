<?php

namespace Tests\Unit\Bom;

use PHPUnit\Framework\TestCase;
use App\Services\BOM\UnitConverter;

class UnitConverterTest extends TestCase
{
    public function test_length_conversions()
    {
        $this->assertEquals(1.0, UnitConverter::convert(1000, 'mm', 'm'));
        $this->assertEquals(1500, UnitConverter::convert(1.5, 'm', 'mm'));
        $this->assertEquals(254, UnitConverter::convert(10, 'inch', 'mm'));
    }

    public function test_area_conversions()
    {
        $this->assertEquals(1.0, UnitConverter::convert(1000000, 'sqmm', 'sqm'));
        $this->assertEquals(0.5, UnitConverter::convert(500000, 'sqmm', 'sqm'));
    }

    public function test_same_unit_returns_original_value()
    {
        $this->assertEquals(123.45, UnitConverter::convert(123.45, 'm', 'M'));
    }
}
