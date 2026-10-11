<?php

namespace Tests\Unit\Calc;

use App\Services\BOM\UnitConverter;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class UnitConverterDimensionTest extends TestCase
{
    public function test_length_to_count_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UnitConverter::convert(2000, 'mm', 'pcs');
    }

    public function test_area_to_length_is_rejected(): void
    {
        $this->expectException(InvalidArgumentException::class);
        UnitConverter::convert(10, 'sqft', 'mtr');
    }

    public function test_mass_conversion_is_correct(): void
    {
        $this->assertEquals(2.5, UnitConverter::convert(2500, 'g', 'kg'));
        $this->assertEquals(2500, UnitConverter::convert(2.5, 'kg', 'g'));
    }

    public function test_sqft_conversion(): void
    {
        $this->assertEqualsWithDelta(92903.04, UnitConverter::convert(1, 'sqft', 'sqmm'), 0.0001);
        $this->assertEqualsWithDelta(1.0, UnitConverter::convert(92903.04, 'sqmm', 'sqft'), 0.0001);
    }
}
