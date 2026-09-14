<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\Material;
use App\Models\BomRule;
use App\Models\Product;
use App\Models\Design;
use Illuminate\Support\Facades\DB;

class MasterDataSeeder extends Seeder
{
    public function run()
    {
        $company = Company::first();
        if (!$company) {
            $company = Company::create(['name' => 'Default Company', 'is_active' => true]);
        }
        $companyId = $company->id;

        // Clear existing to avoid duplicates if run multiple times
        DB::table('bom_rules')->where('company_id', $companyId)->delete();
        DB::table('materials')->where('company_id', $companyId)->delete();
        DB::table('designs')->where('company_id', $companyId)->delete();
        
        // Ensure a Casement Window product exists
        $product = Product::firstOrCreate(
            ['name' => 'Casement Window', 'company_id' => $companyId],
            ['product_code' => 'CW-01', 'opening_type' => 'Casement', 'status' => 'Active']
        );
        
        // Create Design
        $design = Design::create([
            'company_id' => $companyId,
            'name' => 'Standard Casement Window',
            'code' => 'CSM-STD-01',
            'type' => 'Window',
            'configuration' => ['panels' => 1]
        ]);
        
        $design->products()->attach($product->id);

        // Materials
        $materialsData = [
            ['sku' => 'FRAME-C60', 'name' => '60mm Casement Outer Frame', 'category' => 'Profile', 'uom' => 'mtr', 'cost' => 350.00, 'waste_percent' => 5],
            ['sku' => 'SASH-C60', 'name' => '60mm Casement Sash', 'category' => 'Profile', 'uom' => 'mtr', 'cost' => 400.00, 'waste_percent' => 5],
            ['sku' => 'BEAD-SG', 'name' => 'Single Glazing Bead', 'category' => 'Profile', 'uom' => 'mtr', 'cost' => 80.00, 'waste_percent' => 5],
            ['sku' => 'GLS-CLR-6MM', 'name' => '6mm Clear Float Glass', 'category' => 'Glass', 'uom' => 'sqft', 'cost' => 120.00, 'waste_percent' => 10],
            ['sku' => 'HDW-ESPAG-600', 'name' => 'Espag Handle & Gear 600mm', 'category' => 'Hardware', 'uom' => 'nos', 'cost' => 450.00, 'waste_percent' => 0],
            ['sku' => 'HDW-HINGE-3D', 'name' => '3D Hinge', 'category' => 'Hardware', 'uom' => 'nos', 'cost' => 150.00, 'waste_percent' => 0],
            ['sku' => 'STL-FRAME', 'name' => 'GI Steel Reinforcement Frame', 'category' => 'Steel', 'uom' => 'mtr', 'cost' => 120.00, 'waste_percent' => 5],
            ['sku' => 'STL-SASH', 'name' => 'GI Steel Reinforcement Sash', 'category' => 'Steel', 'uom' => 'mtr', 'cost' => 110.00, 'waste_percent' => 5],
        ];

        $materials = [];
        foreach ($materialsData as $data) {
            $data['company_id'] = $companyId;
            $data['is_active'] = true;
            $materials[$data['sku']] = Material::create($data);
        }

        // BOM Rules using AST
        $rules = [
            [
                'material_id' => $materials['FRAME-C60']->id,
                'component_role' => 'Outer Frame',
                'rule_type' => 'PERIMETER_MULTIPLIER',
                'rule_definition' => [
                    'operation' => 'PERIMETER',
                    'inputs' => ['width', 'height'],
                    'parameters' => ['width_multiplier' => 2, 'height_multiplier' => 2]
                ],
                'unit' => 'mtr',
                'formula' => '(W*2 + H*2) / 1000'
            ],
            [
                'material_id' => $materials['SASH-C60']->id,
                'component_role' => 'Sash Frame',
                'rule_type' => 'PERIMETER_MULTIPLIER',
                'rule_definition' => [
                    'operation' => 'PERIMETER',
                    'inputs' => ['width', 'height'],
                    'parameters' => ['width_multiplier' => 2, 'height_multiplier' => 2]
                ],
                'unit' => 'mtr',
                'formula' => '(W*2 + H*2) / 1000'
            ],
            [
                'material_id' => $materials['GLS-CLR-6MM']->id,
                'component_role' => 'Main Glass',
                'rule_type' => 'AREA_CALCULATION',
                'rule_definition' => [
                    'operation' => 'AREA',
                    'inputs' => ['width', 'height'],
                    'parameters' => ['width_deduction' => 120, 'height_deduction' => 120]
                ],
                'unit' => 'sqft',
                'formula' => '((W-120)*(H-120)) / 92903.04'
            ],
            [
                'material_id' => $materials['HDW-ESPAG-600']->id,
                'component_role' => 'Locking Mechanism',
                'rule_type' => 'FIXED_QUANTITY',
                'rule_definition' => ['operation' => 'FIXED', 'value' => 1],
                'condition_definition' => ['field' => 'height', 'operator' => 'GREATER_THAN', 'value' => 600],
                'unit' => 'nos',
                'formula' => '1 (If H > 600)'
            ],
            [
                'material_id' => $materials['HDW-HINGE-3D']->id,
                'component_role' => 'Hinges',
                'rule_type' => 'FIXED_QUANTITY',
                'rule_definition' => ['operation' => 'FIXED', 'value' => 2],
                'condition_definition' => ['field' => 'height', 'operator' => 'LESS_THAN_OR_EQUAL', 'value' => 1200],
                'unit' => 'nos',
                'formula' => '2 (If H <= 1200)'
            ],
            [
                'material_id' => $materials['HDW-HINGE-3D']->id,
                'component_role' => 'Hinges',
                'rule_type' => 'FIXED_QUANTITY',
                'rule_definition' => ['operation' => 'FIXED', 'value' => 3],
                'condition_definition' => ['field' => 'height', 'operator' => 'GREATER_THAN', 'value' => 1200],
                'unit' => 'nos',
                'formula' => '3 (If H > 1200)'
            ],
        ];

        foreach ($rules as $i => $r) {
            $r['company_id'] = $companyId;
            $r['product_id'] = $product->id;
            $r['design_id'] = $design->id;
            $r['sort_order'] = $i;
            BomRule::create($r);
        }
    }
}
