<?php

namespace App\Services;

use App\DTOs\BomItemDTO;
use App\DTOs\BomSummaryDTO;
use InvalidArgumentException;

class BomEngine
{
    protected GlassEngine $glassEngine;
    protected HardwareRuleEngine $hardwareEngine;

    public function __construct(GlassEngine $glassEngine, HardwareRuleEngine $hardwareEngine)
    {
        $this->glassEngine = $glassEngine;
        $this->hardwareEngine = $hardwareEngine;
    }

    /**
     * Generate complete production-grade fabrication BOM for a window/door item.
     *
     * @param array $item
     * @return array Array representation of BomSummaryDTO
     * @throws InvalidArgumentException
     */
    public function generateBOM(array $item): array
    {
        $w = (float)($item['width'] ?? $item['dimension_w'] ?? 0);
        $h = (float)($item['height'] ?? $item['dimension_h'] ?? 0);
        $qty = (int)($item['qty'] ?? $item['quantity'] ?? 1);

        // 1. STEP 10 — Validation
        if ($w <= 0) {
            throw new InvalidArgumentException("Window width must be greater than zero. Received: {$w}");
        }
        if ($h <= 0) {
            throw new InvalidArgumentException("Window height must be greater than zero. Received: {$h}");
        }
        if ($qty <= 0) {
            throw new InvalidArgumentException("Quantity must be greater than zero. Received: {$qty}");
        }

        $type = $item['opening_type'] ?? $item['profile_system'] ?? 'Casement Outward';
        $color = strtoupper($item['profile_color'] ?? $item['color'] ?? 'WHITE');
        $itemCode = $item['item_code'] ?? 'W1';
        $supplier = $item['supplier'] ?? 'CORA System';

        $lineItems = [];

        // 2. STEP 4 — Profile Calculation
        $perimeterM = (($w + $h) * 2 / 1000) * $qty;
        
        // 2.1 Outer Frame Profile
        $frameLengthM = round($perimeterM, 2);
        $lineItems[] = new BomItemDTO([
            'material_category' => 'Profile',
            'material_name' => 'Outer Frame Profile 60mm Chamber',
            'sku' => 'PRF-60-FRM-01',
            'supplier' => $supplier,
            'unit' => 'Mtr',
            'length' => $w,
            'height' => $h,
            'thickness' => 2.5,
            'quantity' => $frameLengthM,
            'weight_kg' => round($frameLengthM * 1.15, 2),
            'waste_percent' => 5.0,
            'rate' => 180.0,
            'remarks' => "Color: {$color} | Frame Perimeter"
        ]);

        // 2.2 Sash Profile
        $isSliding = str_contains(strtolower($type), 'sliding');
        $isFixed = str_contains(strtolower($type), 'fixed');
        $hasSash = !$isFixed;

        $sashLengthM = 0;
        if ($hasSash) {
            $sashLengthM = round($perimeterM, 2);
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Profile',
                'material_name' => $isSliding ? 'Sliding Sash Profile 60mm' : 'Casement Sash Profile Outward 60mm',
                'sku' => $isSliding ? 'PRF-60-SSH-SLD' : 'PRF-60-SSH-CSM',
                'supplier' => $supplier,
                'unit' => 'Mtr',
                'length' => $w,
                'height' => $h,
                'thickness' => 2.5,
                'quantity' => $sashLengthM,
                'weight_kg' => round($sashLengthM * 1.25, 2),
                'waste_percent' => 5.0,
                'rate' => 210.0,
                'remarks' => "Color: {$color} | Sash Profile"
            ]);
        }

        // 2.3 Mullion / Transom Profile (For windows with split or dimensions > 1400mm)
        $mullionLengthM = 0;
        if ($w > 1400 || $h > 1400 || str_contains(strtolower($type), 'mullion') || str_contains(strtolower($type), 'double')) {
            $mullionLengthM = round(($h / 1000) * $qty, 2);
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Profile',
                'material_name' => 'Mullion Profile 60mm Heavy',
                'sku' => 'PRF-60-MUL-01',
                'supplier' => $supplier,
                'unit' => 'Mtr',
                'length' => $h,
                'thickness' => 2.5,
                'quantity' => $mullionLengthM,
                'weight_kg' => round($mullionLengthM * 1.35, 2),
                'waste_percent' => 5.0,
                'rate' => 225.0,
                'remarks' => "Color: {$color} | Vertical Mullion"
            ]);
        }

        // 3. STEP 5 — Glass BOM
        $thicknessMm = (float)($item['glass_thickness'] ?? 5.0);
        $glassRatePerSqft = (float)($item['glass_rate'] ?? 85.0);
        $glassDetails = $this->glassEngine->calculateGlassDetails($w, $h, $type, $thicknessMm, $glassRatePerSqft, $qty);

        $lineItems[] = new BomItemDTO([
            'material_category' => 'Glass',
            'material_name' => $glassDetails['glass_type'],
            'sku' => "GLS-{$thicknessMm}MM-CLR",
            'supplier' => 'Saint-Gobain Glass',
            'unit' => 'Sq.Ft',
            'width' => $glassDetails['glass_cut_width'],
            'height' => $glassDetails['glass_cut_height'],
            'thickness' => $thicknessMm,
            'quantity' => $glassDetails['glass_area_sqft'],
            'weight_kg' => $glassDetails['glass_weight_kg'],
            'waste_percent' => 3.0,
            'rate' => $glassRatePerSqft,
            'remarks' => "Cut Size: {$glassDetails['formatted_cut_size']}"
        ]);

        // 2.4 Glass Beading Profile
        $beadingLengthM = round(($glassDetails['glass_cut_width'] + $glassDetails['glass_cut_height']) * 2 / 1000 * $qty, 2);
        $lineItems[] = new BomItemDTO([
            'material_category' => 'Profile',
            'material_name' => 'Glass Beading Profile 5mm',
            'sku' => 'PRF-60-BDG-05',
            'supplier' => $supplier,
            'unit' => 'Mtr',
            'length' => $beadingLengthM,
            'quantity' => $beadingLengthM,
            'weight_kg' => round($beadingLengthM * 0.35, 2),
            'waste_percent' => 5.0,
            'rate' => 65.0,
            'remarks' => "Color: {$color} | Snap-in Beading"
        ]);

        // 4. STEP 7 — Steel Reinforcement
        $totalSteelLengthM = round($frameLengthM + $sashLengthM + $mullionLengthM, 2);
        $steelWeightKg = round($totalSteelLengthM * 0.85, 2);
        $lineItems[] = new BomItemDTO([
            'material_category' => 'Steel',
            'material_name' => 'Galvanized Steel Reinforcement 1.5mm Box Channel',
            'sku' => 'STL-1.5-BOX',
            'supplier' => 'Jindal Steel',
            'unit' => 'Mtr',
            'length' => $totalSteelLengthM,
            'thickness' => 1.5,
            'quantity' => $totalSteelLengthM,
            'weight_kg' => $steelWeightKg,
            'waste_percent' => 2.0,
            'rate' => 125.0,
            'remarks' => 'Anti-corrosion Zinc Coated'
        ]);

        // 5. STEP 6 — Hardware BOM
        $hardwareSet = $this->hardwareEngine->selectHardware(
            $type,
            $w,
            $h,
            $glassDetails['glass_weight_kg'] + $steelWeightKg,
            $item['accessories_details'] ?? []
        );

        if ($hardwareSet['Locking'] !== 'NA') {
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Hardware',
                'material_name' => "Locking Mechanism ({$hardwareSet['Locking']})",
                'sku' => 'HWD-LK-MP-01',
                'supplier' => 'CORA Hardware',
                'unit' => 'Pcs',
                'quantity' => 1 * $qty,
                'rate' => 450.0,
                'remarks' => $hardwareSet['Locking']
            ]);
        }

        if ($hardwareSet['Handle Type'] !== 'NA') {
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Hardware',
                'material_name' => "Window/Door Handle ({$hardwareSet['Handle Type']})",
                'sku' => 'HWD-HND-ESP-01',
                'supplier' => 'CORA Hardware',
                'unit' => 'Pcs',
                'quantity' => 1 * $qty,
                'rate' => 280.0,
                'remarks' => "Color: {$hardwareSet['Handle color']}"
            ]);
        }

        if ($hardwareSet['Friction'] !== 'NA') {
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Hardware',
                'material_name' => "Friction Stay Stay Arm ({$hardwareSet['Friction']})",
                'sku' => 'HWD-FRC-STAY',
                'supplier' => 'CORA Hardware',
                'unit' => 'Pair',
                'quantity' => 1 * $qty,
                'rate' => 380.0,
                'remarks' => 'SS304 Heavy Duty Friction Hinge'
            ]);
        }

        if ($hardwareSet['Hinge'] !== 'NA') {
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Hardware',
                'material_name' => "3D/2D Door Hinges ({$hardwareSet['Hinge']})",
                'sku' => 'HWD-HNG-3D',
                'supplier' => 'CORA Hardware',
                'unit' => 'Set',
                'quantity' => 1 * $qty,
                'rate' => 420.0,
                'remarks' => '3D Adjustable Hinge Set'
            ]);
        }

        if ($hardwareSet['Roller'] !== 'NA') {
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Hardware',
                'material_name' => "Sliding Roller Assembly ({$hardwareSet['Roller']})",
                'sku' => 'HWD-RLR-HVY',
                'supplier' => 'CORA Hardware',
                'unit' => 'Pair',
                'quantity' => 2 * $qty,
                'rate' => 160.0,
                'remarks' => 'Heavy Bearing Tandem Roller'
            ]);
        }

        // 6. STEP 8 — Accessories & Consumables
        // 6.1 EPDM Rubber Gasket
        $epdmLengthM = round($perimeterM * 2, 2);
        $lineItems[] = new BomItemDTO([
            'material_category' => 'Accessories',
            'material_name' => 'EPDM Rubber Seal Gasket Black',
            'sku' => 'ACC-EPDM-BLK',
            'supplier' => 'Anand Rubber',
            'unit' => 'Mtr',
            'length' => $epdmLengthM,
            'quantity' => $epdmLengthM,
            'weight_kg' => round($epdmLengthM * 0.08, 2),
            'waste_percent' => 5.0,
            'rate' => 18.0,
            'remarks' => 'Weather Seal Glazing & Frame Gasket'
        ]);

        // 6.2 Wool Pile Weather Brush (For sliding windows)
        if ($isSliding) {
            $woolPileM = round(($h / 1000) * 4 * $qty, 2);
            $lineItems[] = new BomItemDTO([
                'material_category' => 'Accessories',
                'material_name' => 'Wool Pile Weather Seal Strip 7x6mm',
                'sku' => 'ACC-BRSH-WPL',
                'supplier' => 'CORA Accessories',
                'unit' => 'Mtr',
                'length' => $woolPileM,
                'quantity' => $woolPileM,
                'waste_percent' => 5.0,
                'rate' => 12.0,
                'remarks' => 'Draft Excluder Brush'
            ]);
        }

        // 6.3 Silicone Sealant Cartridges
        $siliconeCartridges = (float)ceil($perimeterM / 10.0);
        $lineItems[] = new BomItemDTO([
            'material_category' => 'Consumables',
            'material_name' => 'Weatherproof Silicone Sealant Cartridge 300ml',
            'sku' => 'CNS-SIL-WHT',
            'supplier' => 'McCoy Soudal',
            'unit' => 'Cartridge',
            'quantity' => $siliconeCartridges,
            'rate' => 240.0,
            'remarks' => 'Neutral Cure Perimeter Sealant'
        ]);

        // 6.4 Cleats, Caps & Fasteners
        $lineItems[] = new BomItemDTO([
            'material_category' => 'Accessories',
            'material_name' => 'Corner Joint Cleats Heavy Zinc',
            'sku' => 'ACC-CLT-CRN',
            'supplier' => 'CORA Accessories',
            'unit' => 'Pcs',
            'quantity' => 4 * $qty,
            'rate' => 25.0,
            'remarks' => 'Corner Crimping Cleats'
        ]);

        $lineItems[] = new BomItemDTO([
            'material_category' => 'Accessories',
            'material_name' => 'Water Drainage Caps & Cover Caps',
            'sku' => 'ACC-CAP-DRN',
            'supplier' => 'CORA Accessories',
            'unit' => 'Pcs',
            'quantity' => 2 * $qty,
            'rate' => 10.0,
            'remarks' => 'UV Resistant Drainage Caps'
        ]);

        $screwCount = ceil(($totalSteelLengthM * 4) + 16 * $qty);
        $lineItems[] = new BomItemDTO([
            'material_category' => 'Fasteners',
            'material_name' => 'Self-Drilling Stainless Steel Fastener Screws 4x25mm',
            'sku' => 'FAS-SCR-4X25',
            'supplier' => 'Bolt Nut Corp',
            'unit' => 'Pcs',
            'quantity' => $screwCount,
            'rate' => 2.50,
            'remarks' => 'Reinforcement & Hardware Screws'
        ]);

        $lineItems[] = new BomItemDTO([
            'material_category' => 'Packing',
            'material_name' => 'Surface Protection Security Film Tape 60mm',
            'sku' => 'PCK-TPE-60MM',
            'supplier' => '3M Protective Tape',
            'unit' => 'Mtr',
            'quantity' => $frameLengthM,
            'rate' => 8.0,
            'remarks' => 'Scratch Protection Film'
        ]);

        // 7. STEP 9 — Cost Summary Aggregation
        $profileCost = 0.0;
        $glassCost = 0.0;
        $steelCost = 0.0;
        $hardwareCost = 0.0;
        $accessoriesCost = 0.0;

        foreach ($lineItems as $itemDto) {
            switch ($itemDto->materialCategory) {
                case 'Profile':
                    $profileCost += $itemDto->totalCost;
                    break;
                case 'Glass':
                    $glassCost += $itemDto->totalCost;
                    break;
                case 'Steel':
                    $steelCost += $itemDto->totalCost;
                    break;
                case 'Hardware':
                    $hardwareCost += $itemDto->totalCost;
                    break;
                case 'Accessories':
                case 'Consumables':
                case 'Fasteners':
                case 'Packing':
                default:
                    $accessoriesCost += $itemDto->totalCost;
                    break;
            }
        }

        $areaSqft = $glassDetails['glass_area_sqft'];
        $fabricationCost = round($areaSqft * 45.0, 2); // Labor & Assembly @ ₹45/Sq.Ft
        $packingCost = round($areaSqft * 15.0, 2);     // Bubblewrap & Transport Packing @ ₹15/Sq.Ft

        $summaryDto = new BomSummaryDTO([
            'item_code' => $itemCode,
            'dimensions' => "{$w} x {$h} mm",
            'total_qty' => $qty,
            'total_area_sqft' => $areaSqft,
            'total_weight_kg' => round($glassDetails['glass_weight_kg'] + $steelWeightKg, 3),
            'profile_cost' => round($profileCost, 2),
            'glass_cost' => round($glassCost, 2),
            'steel_cost' => round($steelCost, 2),
            'hardware_cost' => round($hardwareCost, 2),
            'accessories_cost' => round($accessoriesCost, 2),
            'fabrication_cost' => $fabricationCost,
            'packing_cost' => $packingCost,
            'line_items' => $lineItems,
        ]);

        return $summaryDto->toArray();
    }
}
