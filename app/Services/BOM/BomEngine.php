<?php

namespace App\Services\BOM;

use App\Models\Product;
use Exception;

class BomEngine
{
    /**
     * Calculate BOM for a given product and context (e.g. width, height, qty).
     * 
     * Context typically includes:
     * - width (in mm)
     * - height (in mm)
     * - quantity
     * - other product-specific configurations
     */
    public function calculate(Product $product, array $context): array
    {
        // Ensure default context variables
        $rawWidth = (float) ($context['width'] ?? 0);
        $rawHeight = (float) ($context['height'] ?? 0);
        $unit = strtolower(trim($context['unit'] ?? 'mm'));

        // Normalize width and height to millimeters for production rules
        $context['width'] = UnitConverter::convert($rawWidth, $unit, 'mm');
        $context['height'] = UnitConverter::convert($rawHeight, $unit, 'mm');
        $context['raw_width'] = $rawWidth;
        $context['raw_height'] = $rawHeight;
        $context['quantity'] = (float) ($context['quantity'] ?? 1);

        $bomLines = [];
        $explanations = [];

        $designId = $context['design_id'] ?? null;
        
        if ($product->relationLoaded('components')) {
            $components = $product->components->filter(function($comp) use ($designId) {
                return $comp->design_id == $designId || is_null($comp->design_id);
            })->sortBy('sort_order');
        } else {
            $query = $product->components()->with('material')->orderBy('sort_order');
            if ($designId) {
                $query->where(function($q) use ($designId) {
                    $q->where('design_id', $designId)->orWhereNull('design_id');
                });
            } else {
                $query->whereNull('design_id');
            }
            $components = $query->get();
        }

        foreach ($components as $component) {
            $material = $component->material;
            
            if (!$material || !$material->is_active) {
                continue; // Skip inactive or missing materials
            }

            $roleLower = strtolower($component->component_role ?? '');

            // Dynamic customization: Handle Mesh selection
            if (str_contains($roleLower, 'mesh') || (strtolower($material->category ?? '') === 'mesh')) {
                if (isset($context['mesh_type'])) {
                    $mType = strtolower(trim($context['mesh_type']));
                    if ($mType === 'no' || $mType === 'no mesh' || $mType === 'none' || $mType === '') {
                        $explanations[] = "Skipped mesh component [{$component->component_role}] because mesh is disabled.";
                        continue;
                    }
                    $customMesh = \App\Models\Material::where('company_id', $product->company_id)
                        ->where('category', 'Mesh')
                        ->where('is_active', true)
                        ->where(function($q) use ($mType) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%' . $mType . '%'])
                              ->orWhereRaw('? LIKE CONCAT("%", LOWER(name), "%")', [$mType]);
                        })->first();
                    if ($customMesh) {
                        $material = $customMesh;
                    }
                }
            }

            // Dynamic customization: Handle Glass selection
            if (str_contains($roleLower, 'glass') || (strtolower($material->category ?? '') === 'glass')) {
                if (!empty($context['glass_type'])) {
                    $gType = strtolower(trim($context['glass_type']));
                    $customGlass = \App\Models\Material::where('company_id', $product->company_id)
                        ->where('category', 'Glass')
                        ->where('is_active', true)
                        ->where(function($q) use ($gType) {
                            $q->whereRaw('LOWER(name) LIKE ?', ['%' . $gType . '%'])
                              ->orWhereRaw('? LIKE CONCAT("%", LOWER(name), "%")', [$gType]);
                        })->first();
                    if ($customGlass) {
                        $material = $customGlass;
                    }
                }
            }

            // 1. Evaluate Condition
            $conditionDefinition = $component->condition_definition ?? [];
            if (!RuleEngine::evaluateCondition($conditionDefinition, $context)) {
                $explanations[] = "Skipped component [{$component->component_role}] due to condition.";
                continue;
            }

            // 2. Evaluate Rule for base theoretical requirement
            $ruleDefinition = !empty($component->rule_definition) 
                ? $component->rule_definition 
                : (!empty($component->formula) ? $component->formula : []);

            try {
                $theoreticalQty = RuleEngine::evaluateRule($ruleDefinition, $context);
            } catch (Exception $e) {
                $explanations[] = "Error evaluating rule for [{$component->component_role}]: " . $e->getMessage();
                continue;
            }

            // 3. Normalize Unit
            // The rule output might be in mm (like perimeter). The material might be stocked/costed in meters (m).
            // We need to convert the theoreticalQty from component->unit to material->uom.
            $componentUnit = $component->unit ?: 'mm';
            $materialUnit = $material->uom ?: 'nos';

            try {
                $normalizedQty = UnitConverter::convert($theoreticalQty, $componentUnit, $materialUnit);
            } catch (Exception $e) {
                $explanations[] = "Unit conversion error for [{$component->component_role}]: " . $e->getMessage();
                continue;
            }

            // 4. Apply Wastage
            // The prompt says: Theoretical Requirement -> Wastage Rule -> Stock Requirement
            $wastePercent = (float) $material->waste_percent;
            $stockRequirement = $normalizedQty * (1 + ($wastePercent / 100));

            // Multiply by item quantity for total requirement for this line
            $totalRequirement = $stockRequirement * $context['quantity'];

            // 5. Build BOM Line (No Financial Data)
            $bomLines[] = [
                'component_role' => $component->component_role,
                'material_id' => $material->id,
                'material_sku' => $material->sku,
                'material_name' => $material->name,
                'material_uom' => $materialUnit,
                'material_cost' => (float) $material->cost,
                'rule_type' => $component->rule_type,
                'theoretical_qty' => $theoreticalQty,
                'normalized_qty' => $normalizedQty,
                'waste_percent' => $wastePercent,
                'stock_requirement_per_item' => $stockRequirement,
                'total_qty' => $totalRequirement, // for the full context quantity
                // Snapshot of variables used for trace/snapshotting
                'snapshot_context' => [
                    'rule' => $ruleDefinition,
                    'variables_used' => $context
                ]
            ];

            $explanations[] = "Evaluated [{$component->component_role}] ({$material->name}): Rule => {$theoreticalQty} {$componentUnit} -> {$normalizedQty} {$materialUnit} + {$wastePercent}% waste = {$stockRequirement} {$materialUnit} / item.";
        }

        return [
            'bom' => $bomLines,
            'explanations' => $explanations,
        ];
    }
}
