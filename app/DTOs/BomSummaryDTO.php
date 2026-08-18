<?php

namespace App\DTOs;

class BomSummaryDTO
{
    public string $itemCode;
    public string $dimensions;
    public int $totalQty;
    public float $totalAreaSqft;
    public float $totalWeightKg;
    public float $profileCost;
    public float $glassCost;
    public float $steelCost;
    public float $hardwareCost;
    public float $accessoriesCost;
    public float $fabricationCost;
    public float $packingCost;
    public float $totalMaterialCost;
    public float $totalBomCost;
    public array $lineItems;

    public function __construct(array $data = [])
    {
        $this->itemCode = $data['item_code'] ?? 'W1';
        $this->dimensions = $data['dimensions'] ?? '1000 x 1000 mm';
        $this->totalQty = (int)($data['total_qty'] ?? 1);
        $this->totalAreaSqft = (float)($data['total_area_sqft'] ?? 0);
        $this->totalWeightKg = (float)($data['total_weight_kg'] ?? 0);

        $this->profileCost = (float)($data['profile_cost'] ?? 0);
        $this->glassCost = (float)($data['glass_cost'] ?? 0);
        $this->steelCost = (float)($data['steel_cost'] ?? 0);
        $this->hardwareCost = (float)($data['hardware_cost'] ?? 0);
        $this->accessoriesCost = (float)($data['accessories_cost'] ?? 0);
        $this->fabricationCost = (float)($data['fabrication_cost'] ?? 0);
        $this->packingCost = (float)($data['packing_cost'] ?? 0);

        $this->totalMaterialCost = round($this->profileCost + $this->glassCost + $this->steelCost + $this->hardwareCost + $this->accessoriesCost, 2);
        $this->totalBomCost = round($this->totalMaterialCost + $this->fabricationCost + $this->packingCost, 2);

        $this->lineItems = $data['line_items'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'item_code' => $this->itemCode,
            'dimensions' => $this->dimensions,
            'total_qty' => $this->totalQty,
            'total_area_sqft' => $this->totalAreaSqft,
            'total_weight_kg' => $this->totalWeightKg,
            'profile_cost' => $this->profileCost,
            'glass_cost' => $this->glassCost,
            'steel_cost' => $this->steelCost,
            'hardware_cost' => $this->hardwareCost,
            'accessories_cost' => $this->accessoriesCost,
            'fabrication_cost' => $this->fabricationCost,
            'packing_cost' => $this->packingCost,
            'total_material_cost' => $this->totalMaterialCost,
            'total_bom_cost' => $this->totalBomCost,
            'line_items' => array_map(function ($item) {
                return $item instanceof BomItemDTO ? $item->toArray() : $item;
            }, $this->lineItems),
        ];
    }
}
