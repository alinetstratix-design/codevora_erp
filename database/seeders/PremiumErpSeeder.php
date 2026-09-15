<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PremiumErpSeeder extends Seeder
{
    public function run()
    {
        $company = DB::table('companies')->first();
        $companyId = $company ? $company->id : 1;
        
        // 1. Create Materials
        $materialsData = [
            // Profiles
            ['sku' => 'PRF-60-FRM-01', 'name' => 'Outer Frame Profile 60mm Chamber', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 180.0, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.15],
            ['sku' => 'PRF-60-SSH-SLD', 'name' => 'Sliding Sash Profile 60mm', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 210.0, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.25],
            ['sku' => 'PRF-60-MUL', 'name' => 'Mullion Profile 60mm', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 190.0, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.10],
            ['sku' => 'PRF-GB-01', 'name' => 'Glazing Bead', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 45.0, 'waste_percent' => 10.0, 'weight_per_mtr' => 0.25],
            
            // Glass (Using properties to store type and thickness)
            ['sku' => 'GLS-5-CLR', 'name' => '5mm Clear Glass', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 45.0, 'waste_percent' => 15.0, 'properties' => json_encode(['glass_type' => '5mm Clear', 'thickness' => 5])],
            ['sku' => 'GLS-6-TGH', 'name' => '6mm Toughened Glass', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 75.0, 'waste_percent' => 15.0, 'properties' => json_encode(['glass_type' => '6mm Toughened', 'thickness' => 6])],
            ['sku' => 'GLS-8-TGH', 'name' => '8mm Toughened Glass', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 90.0, 'waste_percent' => 15.0, 'properties' => json_encode(['glass_type' => '8mm Toughened', 'thickness' => 8])],
            ['sku' => 'GLS-12-TGH', 'name' => '12mm Toughened Glass', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 135.0, 'waste_percent' => 15.0, 'properties' => json_encode(['glass_type' => '12mm Toughened', 'thickness' => 12])],
            ['sku' => 'GLS-24-DGU', 'name' => 'DGU 24mm', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 220.0, 'waste_percent' => 15.0, 'properties' => json_encode(['glass_type' => 'DGU 24mm', 'thickness' => 24])],
            
            // Mesh
            ['sku' => 'MSH-FBR', 'name' => 'Fiber Mesh', 'category' => 'Mesh', 'uom' => 'SqFt', 'cost' => 20.0, 'waste_percent' => 10.0, 'properties' => json_encode(['mesh_type' => 'Fiber Mesh'])],
            ['sku' => 'MSH-SS', 'name' => 'SS Mesh (Stainless Steel)', 'category' => 'Mesh', 'uom' => 'SqFt', 'cost' => 55.0, 'waste_percent' => 10.0, 'properties' => json_encode(['mesh_type' => 'SS Mesh (Stainless Steel)'])],
            
            // Hardware
            ['sku' => 'HDW-SLD-RLR', 'name' => 'Sliding Rollers Set', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 120.0, 'waste_percent' => 0.0],
            ['sku' => 'HDW-LCK-POP', 'name' => 'Pop-up Lock', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 350.0, 'waste_percent' => 0.0],
            ['sku' => 'HDW-HND-C', 'name' => 'C-Type Handle', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 250.0, 'waste_percent' => 0.0],
            ['sku' => 'HDW-EPDM-GSK', 'name' => 'EPDM Gasket', 'category' => 'Hardware', 'uom' => 'Mtr', 'cost' => 18.0, 'waste_percent' => 5.0],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('materials')->truncate();
        
        $materialMap = [];
        foreach ($materialsData as $data) {
            $data['company_id'] = $companyId;
            $data['created_at'] = now();
            $data['updated_at'] = now();
            if (!isset($data['weight_per_mtr'])) $data['weight_per_mtr'] = 0;
            if (!isset($data['stock_length'])) $data['stock_length'] = 6;
            if (!isset($data['properties'])) $data['properties'] = json_encode([]);
            
            $id = DB::table('materials')->insertGetId($data);
            $materialMap[$data['sku']] = $id;
        }
        
        // 2. Create BOM Rules for Product ID 5 (Track Sliding)
        DB::table('bom_rules')->truncate();
        Schema::enableForeignKeyConstraints();
        
        $productId = 5; // Replace if 5 is not Track Sliding
        
        $bomRules = [
            // Outer Frame: perimeter = (w + h) * 2 / 1000
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'material_id' => $materialMap['PRF-60-FRM-01'],
                'component_role' => 'Outer Frame',
                'rule_type' => 'Formula',
                'unit' => 'Mtr',
                'formula' => '(w + h) * 2 / 1000',
                'sort_order' => 1
            ],
            // Sliding Sash: Assuming 2 sashes for a standard slider = roughly perimeter again
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'material_id' => $materialMap['PRF-60-SSH-SLD'],
                'component_role' => 'Sash Profile',
                'rule_type' => 'Formula',
                'unit' => 'Mtr',
                'formula' => '(w + h) * 2 / 1000',
                'sort_order' => 2
            ],
            // Glazing Bead: Around the glass = perimeter * 1.8 (rough approx)
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'material_id' => $materialMap['PRF-GB-01'],
                'component_role' => 'Glazing Bead',
                'rule_type' => 'Formula',
                'unit' => 'Mtr',
                'formula' => '(w + h) * 2 / 1000 * 1.8',
                'sort_order' => 3
            ],
            // Hardware - Rollers: 1 set per sash (assuming 2 sashes = 2 sets)
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'material_id' => $materialMap['HDW-SLD-RLR'],
                'component_role' => 'Rollers',
                'rule_type' => 'Formula',
                'unit' => 'Nos',
                'formula' => '2',
                'sort_order' => 4
            ],
            // Hardware - Pop-up Lock: 1 per sliding window
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'material_id' => $materialMap['HDW-LCK-POP'],
                'component_role' => 'Lock',
                'rule_type' => 'Formula',
                'unit' => 'Nos',
                'formula' => '1',
                'sort_order' => 5
            ],
            // Gasket: Rubber gasket along perimeter
            [
                'company_id' => $companyId,
                'product_id' => $productId,
                'material_id' => $materialMap['HDW-EPDM-GSK'],
                'component_role' => 'Gasket',
                'rule_type' => 'Formula',
                'unit' => 'Mtr',
                'formula' => '(w + h) * 4 / 1000',
                'sort_order' => 6
            ]
        ];

        foreach ($bomRules as $rule) {
            $rule['created_at'] = now();
            $rule['updated_at'] = now();
            DB::table('bom_rules')->insert($rule);
        }
        
        echo "Successfully seeded Materials and BomRules.\n";
    }
}
