<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GlassEngine;
use App\Services\HardwareRuleEngine;
use App\Services\BomEngine;
use InvalidArgumentException;

class EnginesTest extends TestCase
{
    public function test_glass_engine_calculations()
    {
        $glassEngine = new GlassEngine();
        $details = $glassEngine->calculateGlassDetails(878, 2060, 'Casement Outward', 5.0, 85.0, 1);

        $this->assertEquals(768.0, $details['glass_cut_width']);
        $this->assertEquals(1950.0, $details['glass_cut_height']);
        $this->assertGreaterThan(10, $details['glass_area_sqft']);
        $this->assertGreaterThan(10, $details['glass_weight_kg']);
    }

    public function test_hardware_rule_engine_selection()
    {
        $hardwareEngine = new HardwareRuleEngine();
        $hardware = $hardwareEngine->selectHardware('Casement Outward', 900, 2040, 40.0);

        $this->assertEquals('Multi-point', $hardware['Locking']);
        $this->assertEquals('S1-Friction Stay 18"', $hardware['Friction']);
        $this->assertEquals('S1-3D Hinges (3 Pcs)', $hardware['Hinge']);
    }

    public function test_bom_engine_generates_complete_fabrication_bom()
    {
        $glassEngine = new GlassEngine();
        $hardwareEngine = new HardwareRuleEngine();
        $bomEngine = new BomEngine($glassEngine, $hardwareEngine);

        $item = [
            'item_code' => 'W1',
            'width' => 878,
            'height' => 2060,
            'qty' => 1,
            'opening_type' => 'Casement Outward',
            'profile_color' => 'WHITE'
        ];

        $bom = $bomEngine->generateBOM($item);

        $this->assertEquals('W1', $bom['item_code']);
        $this->assertGreaterThan(5, count($bom['line_items']));
        $this->assertGreaterThan(0, $bom['profile_cost']);
        $this->assertGreaterThan(0, $bom['glass_cost']);
        $this->assertGreaterThan(0, $bom['steel_cost']);
        $this->assertGreaterThan(0, $bom['hardware_cost']);
        $this->assertGreaterThan(0, $bom['total_bom_cost']);
    }

    public function test_bom_engine_validates_invalid_dimensions()
    {
        $this->expectException(InvalidArgumentException::class);

        $glassEngine = new GlassEngine();
        $hardwareEngine = new HardwareRuleEngine();
        $bomEngine = new BomEngine($glassEngine, $hardwareEngine);

        $bomEngine->generateBOM([
            'width' => -100,
            'height' => 2000,
            'qty' => 1
        ]);
    }
}
