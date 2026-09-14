<?php

namespace App\Services\BOM;

use App\Models\Material;

class CostingEngine
{
    /**
     * Applies material costing to the calculated BOM result.
     * 
     * @param array $bomResult The output from BomEngine->calculate()
     * @return array The calculated cost result
     */
    public function calculate(array $bomResult): array
    {
        $costedItems = [];
        $materialTotal = 0.0;
        $hasZeroCostMaterials = false;

        foreach ($bomResult['bom'] ?? [] as $bomLine) {
            // Cost is now passed securely from the BomEngine which eager loads materials.
            $unitCost = (float) ($bomLine['material_cost'] ?? 0);
            
            // Default to 0 safely without crashing the system
            if ($unitCost <= 0) {
                $unitCost = 0;
                $hasZeroCostMaterials = true;
            }
            
            $extendedCost = $bomLine['total_qty'] * $unitCost;
            $materialTotal += $extendedCost;

            $costedItems[] = array_merge($bomLine, [
                'unit_cost' => $unitCost,
                'total_cost' => $extendedCost
            ]);
        }

        return [
            'items' => $costedItems,
            'material_total' => $materialTotal,
            'has_zero_cost_materials' => $hasZeroCostMaterials,
        ];
    }
}
