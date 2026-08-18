<?php

namespace App\DTOs;

class BomItemDTO
{
    public string $materialCategory;
    public string $materialName;
    public string $sku;
    public string $supplier;
    public string $unit;
    public float $length;
    public float $width;
    public float $height;
    public float $thickness;
    public float $quantity;
    public float $weightKg;
    public float $wastePercent;
    public float $rate;
    public float $totalCost;
    public string $remarks;

    public function __construct(array $data = [])
    {
        $this->materialCategory = $data['material_category'] ?? 'Profile';
        $this->materialName = $data['material_name'] ?? 'Generic Material';
        $this->sku = $data['sku'] ?? 'SKU-GENERIC';
        $this->supplier = $data['supplier'] ?? 'CORA System';
        $this->unit = $data['unit'] ?? 'Pcs';
        $this->length = (float)($data['length'] ?? 0);
        $this->width = (float)($data['width'] ?? 0);
        $this->height = (float)($data['height'] ?? 0);
        $this->thickness = (float)($data['thickness'] ?? 0);
        $this->quantity = (float)($data['quantity'] ?? 1);
        $this->weightKg = (float)($data['weight_kg'] ?? 0);
        $this->wastePercent = (float)($data['waste_percent'] ?? 0);
        $this->rate = (float)($data['rate'] ?? 0);

        // Total Cost includes cutting/material waste allowance
        $rawCost = $this->rate * $this->quantity;
        $this->totalCost = round($rawCost * (1 + ($this->wastePercent / 100)), 2);
        $this->remarks = $data['remarks'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'material_category' => $this->materialCategory,
            'material_name' => $this->materialName,
            'sku' => $this->sku,
            'supplier' => $this->supplier,
            'unit' => $this->unit,
            'length' => $this->length,
            'width' => $this->width,
            'height' => $this->height,
            'thickness' => $this->thickness,
            'quantity' => $this->quantity,
            'weight_kg' => $this->weightKg,
            'waste_percent' => $this->wastePercent,
            'rate' => $this->rate,
            'total_cost' => $this->totalCost,
            'remarks' => $this->remarks,
        ];
    }
}
