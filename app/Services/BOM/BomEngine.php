<?php

namespace App\Services\BOM;

use App\Models\Material;
use App\Models\Product;
use Exception;

class BomEngine
{
    /**
     * Calculate BOM for a given product and context (width, height, quantity, options).
     *
     * Returns:
     *  - bom:          calculated lines (no financial data)
     *  - explanations: human-readable trace of each evaluated component
     *  - errors:       configuration problems that make the BOM incomplete (must block saving)
     */
    public function calculate(Product $product, array $context): array
    {
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
        $errors = [];

        $designId = $context['design_id'] ?? null;

        if ($product->relationLoaded('components')) {
            $components = $product->components->filter(function ($comp) use ($designId) {
                return $comp->design_id == $designId || is_null($comp->design_id);
            })->sortBy('sort_order');
        } else {
            $query = $product->components()->with('material')->orderBy('sort_order');
            if ($designId) {
                $query->where(function ($q) use ($designId) {
                    $q->where('design_id', $designId)->orWhereNull('design_id');
                });
            } else {
                $query->whereNull('design_id');
            }
            $components = $query->get();
        }

        foreach ($components as $component) {
            $role = $component->component_role ?: ('rule #' . $component->id);
            $material = $component->material;

            if (!$material) {
                $errors[] = "Component [{$role}] has no material assigned (it may have been deleted).";
                continue;
            }

            if (!$material->is_active) {
                // An inactive material on a required component would silently drop its cost: treat as an error.
                $errors[] = "Material [{$material->name}] for [{$role}] is inactive. Activate it or change the BOM.";
                continue;
            }

            $roleLower = strtolower($component->component_role ?? '');

            // Dynamic customization: mesh selection
            if (str_contains($roleLower, 'mesh') || (strtolower($material->category ?? '') === 'mesh')) {
                if (isset($context['mesh_type'])) {
                    $mType = strtolower(trim($context['mesh_type']));
                    if ($mType === 'no' || $mType === 'no mesh' || $mType === 'none' || $mType === '') {
                        $explanations[] = "Skipped mesh component [{$role}] because mesh is disabled.";
                        continue;
                    }
                    $customMesh = $this->findCompanyMaterial($product, 'Mesh', $mType);
                    if ($customMesh) {
                        $material = $customMesh;
                    }
                }
            }

            // Dynamic customization: glass selection
            if (str_contains($roleLower, 'glass') || (strtolower($material->category ?? '') === 'glass')) {
                if (!empty($context['glass_type'])) {
                    $gType = strtolower(trim($context['glass_type']));
                    $customGlass = $this->findCompanyMaterial($product, 'Glass', $gType);
                    if ($customGlass) {
                        $material = $customGlass;
                    }
                }
            }

            // 1. Condition
            $conditionDefinition = $component->condition_definition ?? [];
            try {
                if (!RuleEngine::evaluateCondition($conditionDefinition, $context)) {
                    $explanations[] = "Skipped component [{$role}] due to condition.";
                    continue;
                }
            } catch (Exception $e) {
                $errors[] = "Condition error for [{$role}]: " . $e->getMessage();
                continue;
            }

            // 2. Theoretical requirement
            $ruleDefinition = !empty($component->rule_definition)
                ? $component->rule_definition
                : (!empty($component->formula) ? $component->formula : []);

            try {
                $theoreticalQty = RuleEngine::evaluateRule($ruleDefinition, $context);
            } catch (Exception $e) {
                $errors[] = "Rule error for [{$role}]: " . $e->getMessage();
                continue;
            }

            // 3. Normalize unit (rule output unit -> material stock unit)
            $componentUnit = $component->unit ?: 'mm';
            $materialUnit = $material->uom ?: 'nos';

            try {
                $normalizedQty = UnitConverter::convert($theoreticalQty, $componentUnit, $materialUnit);
            } catch (Exception $e) {
                $errors[] = "Unit error for [{$role}]: " . $e->getMessage();
                continue;
            }

            // 4. Wastage: theoretical -> stock requirement
            $wastePercent = (float) $material->waste_percent;
            $stockRequirement = $normalizedQty * (1 + ($wastePercent / 100));

            // 5. Multiply by item quantity
            $totalRequirement = $stockRequirement * $context['quantity'];

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
                'total_qty' => $totalRequirement,
                'snapshot_context' => [
                    'rule' => $ruleDefinition,
                    'variables_used' => $context,
                ],
            ];

            $explanations[] = "Evaluated [{$role}] ({$material->name}): Rule => {$theoreticalQty} {$componentUnit} -> {$normalizedQty} {$materialUnit} + {$wastePercent}% waste = {$stockRequirement} {$materialUnit} / item.";
        }

        return [
            'bom' => $bomLines,
            'explanations' => $explanations,
            'errors' => $errors,
        ];
    }

    /**
     * Find an active material of the product's own company whose name matches the selected option.
     */
    private function findCompanyMaterial(Product $product, string $category, string $selected): ?Material
    {
        return Material::withoutGlobalScopes()
            ->where('company_id', $product->company_id)
            ->where('category', $category)
            ->where('is_active', true)
            ->where('name', '<>', '')
            ->where(function ($q) use ($selected) {
                $q->whereRaw('LOWER(name) LIKE ?', ['%' . $selected . '%'])
                  ->orWhereRaw('? LIKE CONCAT(\'%\', LOWER(name), \'%\')', [$selected]);
            })
            ->orderBy('id')
            ->first();
    }
}
