<?php

namespace App\DTOs;

class ItemDTO
{
    public int|string $id;
    public string $itemCode;
    public string $name;
    public string $position;
    public string $profileSystem;
    public string $location;

    public float $width;
    public float $height;
    public string $formattedWidth;
    public string $formattedHeight;
    public string $formattedSize;
    public string $unit;

    public float $sqftArea;
    public string $formattedSqftArea;

    public float $unitPrice;
    public string $formattedUnitPrice;

    public float $valuePerSqft;
    public string $formattedValuePerSqft;

    public int $quantity;
    public string $formattedQuantity;

    public float $totalValue;
    public string $formattedTotalValue;

    public float $weightKg;
    public string $formattedWeightKg;

    public DrawingDTO $drawing;
    public ProfileDTO $profile;
    public GlassDTO $glass;
    public HardwareDTO $hardware;
    public AccessoryDTO $accessory;

    public array $commercialSpecs;
    public array $inclusions;
    public array $exclusions;

    public string $notes;

    public function __construct(array $data = [], DrawingDTO $drawing = null, ProfileDTO $profile = null, GlassDTO $glass = null, HardwareDTO $hardware = null, AccessoryDTO $accessory = null)
    {
        $this->id = $data['id'] ?? rand(1000, 9999);
        $this->itemCode = $data['item_code'] ?? $data['code'] ?? 'W1';
        $this->name = $data['product_name'] ?? $data['system_name'] ?? $this->itemCode;
        $this->position = $data['position'] ?? $this->itemCode;
        $this->profileSystem = $data['profile_system'] ?? 'CORA - 60MM CASEMENT SERIES';
        $this->location = $data['location'] ?? 'Master Bedroom';

        $this->width = (float)($data['width'] ?? $data['dimension_w'] ?? 0);
        $this->height = (float)($data['height'] ?? $data['dimension_h'] ?? 0);
        $this->unit = $data['unit'] ?? 'mm';

        $this->formattedWidth = number_format($this->width, 2, '.', '');
        $this->formattedHeight = number_format($this->height, 2, '.', '');
        $this->formattedSize = "W = {$this->formattedWidth}; H = {$this->formattedHeight}";

        $this->sqftArea = (float)($data['area'] ?? 0);
        $this->formattedSqftArea = number_format($this->sqftArea, 3, '.', '') . ' Sq.Ft.';

        $this->unitPrice = (float)($data['unit_price'] ?? 0);
        $this->formattedUnitPrice = number_format($this->unitPrice, 2, '.', ',') . ' INR';

        $this->valuePerSqft = (float)($data['value_per_sqft'] ?? 0);
        $this->formattedValuePerSqft = number_format($this->valuePerSqft, 2, '.', ',') . ' INR';

        $this->quantity = (int)($data['qty'] ?? $data['quantity'] ?? 1);
        $this->formattedQuantity = $this->quantity . ' Pcs';

        $this->totalValue = (float)($data['amount'] ?? $data['total_cost'] ?? 0);
        $this->formattedTotalValue = number_format($this->totalValue, 2, '.', ',') . ' INR';

        $this->weightKg = (float)($data['weight_kg'] ?? 0);
        $this->formattedWeightKg = number_format($this->weightKg, 3, '.', '') . ' KG';

        $this->notes = $data['notes'] ?? $data['remarks'] ?? '';

        $this->drawing = $drawing ?? new DrawingDTO();
        $this->profile = $profile ?? new ProfileDTO($data['profile_details'] ?? []);
        $this->glass = $glass ?? new GlassDTO($data);
        $this->hardware = $hardware ?? new HardwareDTO($data);
        $this->accessory = $accessory ?? new AccessoryDTO($data['accessories_details'] ?? []);

        // 35 Customer-Facing Commercial Specifications
        $this->commercialSpecs = [
            'Window / Door Type' => $data['opening_type'] ?? 'Casement Window Outward',
            'Profile Brand' => $data['profile_brand'] ?? 'CORA',
            'Profile Series' => $this->profileSystem,
            'Wall Thickness' => '2.5 mm Class A',
            'Insulation Chambers' => '3-Chamber High Thermal',
            'Steel Reinforcement' => 'Yes (Galvanized Steel 1.5mm Box)',
            'Glass Make' => 'Saint-Gobain Glass',
            'Glass Type' => $this->glass->type ?? '5mm Clear Toughened',
            'Glass Thickness' => '5.0 mm',
            'Glass Performance' => 'Acoustic & Solar Control',
            'Mosquito Mesh' => $data['mesh_type'] ?? 'No Mesh',
            'Hardware Brand' => $this->hardware->brand ?? 'CORA Hardware',
            'Hardware Finish' => 'White Powder Coated',
            'Locking System' => 'Multi-point Security Lock',
            'Handle Type' => 'Ergonomic Espag Handle',
            'Hinges / Stays' => 'SS304 Friction Hinge Stay',
            'Drainage System' => 'Concealed Water Drainage Caps',
            'Warranty' => '10 Years Profile / 1 Year Hardware',
            'Delivery Timeline' => '2 to 3 Weeks from Order Confirmation',
        ];

        // Customer Inclusions List
        $this->inclusions = [
            '✓ Complete Window & Frame Assembly',
            '✓ Toughened Glass & Snap-in Beading',
            '✓ Multi-point Security Locking System',
            '✓ Espag Handle & SS Friction Stays',
            '✓ On-site Professional Installation',
            '✓ Weatherproof Silicone Perimeter Seal',
            '✓ Stainless Steel Fasteners & Screws',
            '✓ EPDM Rubber Weather Seals',
        ];

        // Customer Exclusions List
        $this->exclusions = [
            '✗ Civil & Masonry Alteration Work',
            '✗ Electrical & Wiring Work',
            '✗ Wall Painting & Touch-up Work',
            '✗ POP, Tile & Granite Repair',
            '✗ External Scaffolding & Heavy Crane',
            '✗ On-site Power Generator',
        ];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'item_code' => $this->itemCode,
            'name' => $this->name,
            'position' => $this->position,
            'profile_system' => $this->profileSystem,
            'location' => $this->location,
            'width' => $this->width,
            'height' => $this->height,
            'formatted_size' => $this->formattedSize,
            'unit' => $this->unit,
            'sqft_area' => $this->sqftArea,
            'formatted_sqft_area' => $this->formattedSqftArea,
            'unit_price' => $this->unitPrice,
            'formatted_unit_price' => $this->formattedUnitPrice,
            'value_per_sqft' => $this->valuePerSqft,
            'formatted_value_per_sqft' => $this->formattedValuePerSqft,
            'quantity' => $this->quantity,
            'formatted_quantity' => $this->formattedQuantity,
            'total_value' => $this->totalValue,
            'formatted_total_value' => $this->formattedTotalValue,
            'weight_kg' => $this->weightKg,
            'formatted_weight_kg' => $this->formattedWeightKg,
            'drawing' => $this->drawing->toArray(),
            'profile' => $this->profile->toArray(),
            'glass' => $this->glass->toArray(),
            'hardware' => $this->hardware->toArray(),
            'accessory' => $this->accessory->toArray(),
            'commercial_specs' => $this->commercialSpecs,
            'inclusions' => $this->inclusions,
            'exclusions' => $this->exclusions,
            'notes' => $this->notes,
        ];
    }
}
