<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\QuotationCalculationService;
use App\Services\BOM\UnitConverter;
use App\Services\BOM\BomEngine;
use App\Helpers\SvgGenerator;
use App\DTOs\ItemDTO;
use App\Models\Product;

class MeasurementUnitCustomizationTest extends TestCase
{
    public function test_calculate_area_supports_all_units()
    {
        $calc = app(QuotationCalculationService::class);

        // 1. Millimeters: 1200mm x 1500mm = 1,800,000 mm² / 92,903.04 = 19.375 Sq.Ft.
        $areaMm = $calc->calculateArea(1200, 1500, 'mm');
        $this->assertEquals(19.375, $areaMm);

        // 2. Inches: 48 in x 60 in = 2880 in² / 144 = 20.000 Sq.Ft.
        $areaInch = $calc->calculateArea(48, 60, 'inch');
        $this->assertEquals(20.0, $areaInch);

        // 3. Feet: 4 ft x 5 ft = 20.000 Sq.Ft.
        $areaFt = $calc->calculateArea(4, 5, 'ft');
        $this->assertEquals(20.0, $areaFt);

        // 4. Centimeters: 120 cm x 150 cm = 18000 cm² / 929.0304 = 19.375 Sq.Ft.
        $areaCm = $calc->calculateArea(120, 150, 'cm');
        $this->assertEquals(19.375, $areaCm);
    }

    public function test_unit_converter_converts_custom_units_to_mm()
    {
        // 1 inch = 25.4 mm
        $this->assertEquals(254.0, UnitConverter::convert(10, 'inch', 'mm'));
        // 1 ft = 304.8 mm
        $this->assertEquals(304.8, UnitConverter::convert(1, 'ft', 'mm'));
        // 1 cm = 10 mm
        $this->assertEquals(100.0, UnitConverter::convert(10, 'cm', 'mm'));
    }

    public function test_svg_generator_displays_custom_unit_labels()
    {
        $svgInch = SvgGenerator::generateWindowDrawing(48, 60, 'inch', 'Casement Series');
        $this->assertStringContainsString('48.00 in', $svgInch);
        $this->assertStringContainsString('60.00 in', $svgInch);

        $svgFt = SvgGenerator::generateWindowDrawing(4, 5, 'ft', 'Casement Series');
        $this->assertStringContainsString('4.00 ft', $svgFt);
        $this->assertStringContainsString('5.00 ft', $svgFt);

        $svgCm = SvgGenerator::generateWindowDrawing(120, 150, 'cm', 'Casement Series');
        $this->assertStringContainsString('120.00 cm', $svgCm);
        $this->assertStringContainsString('150.00 cm', $svgCm);

        $svgMm = SvgGenerator::generateWindowDrawing(1200, 1500, 'mm', 'Casement Series');
        $this->assertStringContainsString('1200.00 mm', $svgMm);
        $this->assertStringContainsString('1500.00 mm', $svgMm);
    }

    public function test_item_dto_preserves_and_formats_custom_unit()
    {
        $dtoInch = new ItemDTO([
            'width' => 48,
            'height' => 60,
            'unit' => 'inch',
            'area' => 20.0
        ]);
        $this->assertEquals('inch', $dtoInch->unit);
        $this->assertEquals('W = 48.00 inch; H = 60.00 inch', $dtoInch->formattedSize);

        $dtoFt = new ItemDTO([
            'width' => 4,
            'height' => 5,
            'unit' => 'ft',
            'area' => 20.0
        ]);
        $this->assertEquals('ft', $dtoFt->unit);
        $this->assertEquals('W = 4.00 ft; H = 5.00 ft', $dtoFt->formattedSize);
    }
}
