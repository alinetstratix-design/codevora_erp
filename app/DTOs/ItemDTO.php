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
    public array $sizes;

    /** @var array<int, array{svg_base64: string, label: string}> One drawing per size (multi-size items only) */
    public array $sizeDrawings = [];

    public string $notes;

    public function __construct(array $data = [], DrawingDTO $drawing = null, ProfileDTO $profile = null, GlassDTO $glass = null, HardwareDTO $hardware = null, AccessoryDTO $accessory = null)
    {
        $this->id = $data['id'] ?? rand(1000, 9999);
        $this->itemCode = $data['item_code'] ?? $data['code'] ?? 'W1';
        $this->name = $data['product_name'] ?? $data['system_name'] ?? $this->itemCode;
        $this->position = $data['position'] ?? $this->itemCode;
        $this->profileSystem = $data['profile_system'] ?? $data['profile_series'] ?? $data['system_name'] ?? '—';
        $this->location = $data['location'] ?? '—';

        $this->width = (float)($data['width'] ?? $data['dimension_w'] ?? 0);
        $this->height = (float)($data['height'] ?? $data['dimension_h'] ?? 0);
        $this->unit = $data['unit'] ?? 'mm';

        $this->formattedWidth = number_format($this->width, 2, '.', '');
        $this->formattedHeight = number_format($this->height, 2, '.', '');
        $this->formattedSize = "W = {$this->formattedWidth} {$this->unit}; H = {$this->formattedHeight} {$this->unit}";

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
        $this->sizes = $data['sizes'] ?? [];
        $this->sizeDrawings = $data['size_drawings'] ?? [];

        $this->drawing = $drawing ?? new DrawingDTO();
        $this->profile = $profile ?? new ProfileDTO($data['profile_details'] ?? []);
        $this->glass = $glass ?? new GlassDTO($data);
        $this->hardware = $hardware ?? new HardwareDTO($data);
        $this->accessory = $accessory ?? new AccessoryDTO($data['accessories_details'] ?? []);

        // Company-owned specifications come from Company Settings. Missing values print as a dash.
        $company = $data['company_specs'] ?? [];
        $v = static function ($value): string {
            return ($value === null || trim((string) $value) === '') ? '—' : (string) $value;
        };

        $this->commercialSpecs = [
            'Window / Door Type' => $v($data['opening_type'] ?? null),
            'Profile Brand' => $v($data['profile_brand'] ?? null),
            'Profile Series' => $this->profileSystem,
            'Profile Color' => $v($this->profile->color ?? ($data['color'] ?? ($data['profile_color'] ?? null))),
            'Wall Thickness' => $v($company['wall_thickness'] ?? null),
            'Insulation Chambers' => $v($company['insulation_chambers'] ?? null),
            'Steel Reinforcement' => $v($company['steel_reinforcement'] ?? null),
            'Glass Make' => $v($company['glass_make'] ?? null),
            'Glass Type' => $v($this->glass->type ?? null),
            'Glass Thickness' => $v($this->glass->thickness ?? ($data['glass_thickness'] ?? null)),
            'Glass Performance' => $v($company['glass_performance'] ?? null),
            'Mosquito Mesh' => $v($data['mesh_type'] ?? ($this->profile->meshType ?? 'No Mesh')),
            'Hardware Brand' => $v($this->hardware->brand),
            'Hardware Finish' => $v($this->hardware->color),
            'Locking System' => $v($company['locking_system'] ?? null),
            'Handle Type' => $v($data['handle_type'] ?? null),
            'Hinges / Stays' => $v($company['hinges_stays'] ?? null),
            'Drainage System' => $v($company['drainage_system'] ?? null),
            'Warranty' => $v($company['warranty'] ?? null),
            'Delivery Timeline' => $v($company['delivery_timeline'] ?? null),
        ];

        // Customer Inclusions List
        $this->inclusions = array_values(array_filter($company['inclusions'] ?? [], fn ($line) => trim((string) $line) !== ''));

        // Customer Exclusions List
        $this->exclusions = array_values(array_filter($company['exclusions'] ?? [], fn ($line) => trim((string) $line) !== ''));
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
            'sizes' => $this->sizes,
            'size_drawings' => $this->sizeDrawings,
            'notes' => $this->notes,
        ];
    }
}
