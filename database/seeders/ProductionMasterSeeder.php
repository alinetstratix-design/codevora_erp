<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Material;
use App\Models\Product;
use App\Models\Design;
use App\Models\BomRule;

class ProductionMasterSeeder extends Seeder
{
    public function run()
    {
        // 1. Resolve Company
        $company = Company::first();
        if (!$company) {
            $company = Company::create(['name' => 'SHAH ENTERPRISES', 'is_active' => 1]);
        } else {
            $company->update(['name' => 'SHAH ENTERPRISES', 'is_active' => 1]);
        }
        $companyId = $company->id; // 14

        // 2. Link & Ensure CompanySetting
        $setting = CompanySetting::find(10) ?? CompanySetting::where('company_id', $companyId)->first() ?? CompanySetting::first();
        if (!$setting) {
            $setting = new CompanySetting();
        }
        $setting->company_id = $companyId;
        $setting->company_name = 'SHAH ENTERPRISES';
        $setting->email = 'shahenterprises044@gmail.com';
        $setting->phone = '+91 9599543500';
        $setting->website = 'https://www.codevora.com';
        $setting->address = 'Industrial Area, Phase 2, New Delhi - 110020';
        $setting->gst_number = '07AABCS1429B1Z1';
        $setting->gstin = '07AABCS1429B1Z1';
        $setting->save();

        // 3. Clean and Populate Production Materials for this company
        Schema::disableForeignKeyConstraints();
        BomRule::where('company_id', $companyId)->delete();
        Material::where('company_id', $companyId)->delete();

        $materials = [
            // Profiles
            ['sku' => 'PRF-UPVC-FRM60', 'name' => 'uPVC Outer Frame Profile 60mm', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 185.00, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.18],
            ['sku' => 'PRF-UPVC-SLD-SSH', 'name' => 'uPVC Sliding Sash Profile 60mm', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 215.00, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.25],
            ['sku' => 'PRF-UPVC-CSM-SSH', 'name' => 'uPVC Casement Sash Profile 60mm', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 205.00, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.22],
            ['sku' => 'PRF-UPVC-GB', 'name' => 'uPVC Glazing Bead Profile', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 45.00, 'waste_percent' => 8.0, 'weight_per_mtr' => 0.25],
            ['sku' => 'PRF-UPVC-3T-FRM', 'name' => 'uPVC 3-Track Outer Frame Profile', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 275.00, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.70],
            ['sku' => 'PRF-UPVC-MSH-SSH', 'name' => 'uPVC Mosquito Mesh Sash Profile', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 145.00, 'waste_percent' => 5.0, 'weight_per_mtr' => 0.85],
            ['sku' => 'PRF-ALU-SLIM45', 'name' => 'Aluminium Slimline 45mm Frame', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 320.00, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.45],
            ['sku' => 'PRF-ALU-SLIM-SSH', 'name' => 'Aluminium Slimline Sash Profile', 'category' => 'Profile', 'uom' => 'Mtr', 'cost' => 340.00, 'waste_percent' => 5.0, 'weight_per_mtr' => 1.55],

            // Glasses
            ['sku' => 'GLS-5MM-TGH', 'name' => '5mm Clear Toughened Glass', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 52.00, 'waste_percent' => 12.0, 'properties' => json_encode(['glass_type' => '5mm Clear', 'thickness' => 5])],
            ['sku' => 'GLS-6MM-TGH', 'name' => '6mm Clear Toughened Glass', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 78.00, 'waste_percent' => 12.0, 'properties' => json_encode(['glass_type' => '6mm Toughened', 'thickness' => 6])],
            ['sku' => 'GLS-8MM-TGH', 'name' => '8mm Clear Toughened Glass', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 105.00, 'waste_percent' => 12.0, 'properties' => json_encode(['glass_type' => '8mm Toughened', 'thickness' => 8])],
            ['sku' => 'GLS-24MM-DGU', 'name' => 'DGU 24mm Insulated Glass (5+14A+5)', 'category' => 'Glass', 'uom' => 'SqFt', 'cost' => 240.00, 'waste_percent' => 12.0, 'properties' => json_encode(['glass_type' => 'DGU 24mm', 'thickness' => 24])],

            // Meshes
            ['sku' => 'MSH-SS304', 'name' => 'SS 304 High Tensile Mosquito Mesh', 'category' => 'Mesh', 'uom' => 'SqFt', 'cost' => 55.00, 'waste_percent' => 10.0, 'properties' => json_encode(['mesh_type' => 'SS Mesh (Stainless Steel)'])],
            ['sku' => 'MSH-FIBER', 'name' => 'Fiberglass Mosquito Mesh', 'category' => 'Mesh', 'uom' => 'SqFt', 'cost' => 22.00, 'waste_percent' => 10.0, 'properties' => json_encode(['mesh_type' => 'Fiber Mesh'])],

            // Hardware & Accessories
            ['sku' => 'HDW-SLD-RLR-SET', 'name' => 'Heavy Duty Nylon Bearing Rollers (Pair)', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 175.00, 'waste_percent' => 0.0],
            ['sku' => 'HDW-TOUCH-LCK', 'name' => 'Single Point Concealed Touch Lock', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 195.00, 'waste_percent' => 0.0],
            ['sku' => 'HDW-ESPAG-SET', 'name' => 'Multi-Point Espag Transmission & Handle', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 480.00, 'waste_percent' => 0.0],
            ['sku' => 'HDW-FRIC-STAY', 'name' => 'SS 304 12-inch Friction Stay Hinges (Pair)', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 290.00, 'waste_percent' => 0.0],
            ['sku' => 'HDW-DR-HANDLE', 'name' => 'Heavy Duty D-Handle with Key Cylinder', 'category' => 'Hardware', 'uom' => 'Nos', 'cost' => 650.00, 'waste_percent' => 0.0],
            ['sku' => 'ACC-EPDM-GSK', 'name' => 'EPDM Glazing Weather Gasket', 'category' => 'Hardware', 'uom' => 'Mtr', 'cost' => 15.00, 'waste_percent' => 5.0],
            ['sku' => 'ACC-WOOL-PILE', 'name' => 'Fin Seal Weather Wool Pile', 'category' => 'Hardware', 'uom' => 'Mtr', 'cost' => 9.50, 'waste_percent' => 5.0],
        ];

        $matMap = [];
        foreach ($materials as $m) {
            $m['company_id'] = $companyId;
            $m['created_at'] = now();
            $m['updated_at'] = now();
            $mat = Material::create($m);
            $matMap[$m['sku']] = $mat->id;
        }

        // 4. Production Vector SVG Designs (Bound inside responsive viewBox with margin for dimensions)
        $sliding2TrackSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="{{VB_X}} {{VB_Y}} {{VB_WIDTH}} {{VB_HEIGHT}}" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
    <defs>
        <marker id="arr-start" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
            <path d="M5,0 L0,3 L5,6 z" fill="#1b305b" />
        </marker>
        <marker id="arr-end" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
            <path d="M0,0 L5,3 L0,6 z" fill="#1b305b" />
        </marker>
        <marker id="arrow-slide" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto">
            <path d="M 0 0 L 10 5 L 0 10 z" fill="#e74c3c" />
        </marker>
    </defs>
    <g class="window-frame">
        <!-- Outer Frame -->
        <rect x="0" y="0" width="{{WIDTH}}" height="{{HEIGHT}}" fill="#f8fafc" stroke="#1e293b" stroke-width="32" rx="4"/>
        
        <!-- Left Panel (Fixed/Slider) -->
        <rect x="36" y="36" width="{{PANEL_WIDTH}}" height="{{INNER_HEIGHT}}" fill="#e0f2fe" stroke="#334155" stroke-width="16" rx="2"/>
        <text x="54" y="66" font-family="Arial" font-size="24" font-weight="bold" fill="#0284c7">S1</text>
        
        <!-- Right Panel (Active Slider) -->
        <rect x="{{PANEL_2_X}}" y="36" width="{{PANEL_WIDTH}}" height="{{INNER_HEIGHT}}" fill="#bae6fd" stroke="#0f172a" stroke-width="18" rx="2"/>
        <text x="{{PANEL_2_X}} + 18" y="66" font-family="Arial" font-size="24" font-weight="bold" fill="#0369a1">S2</text>
        
        <!-- Slide Indicator Arrow -->
        <line x1="{{CENTER_X}}" y1="{{CENTER_Y}}" x2="{{SLIDE_ARROW_END}}" y2="{{CENTER_Y}}" stroke="#e74c3c" stroke-width="8" marker-end="url(#arrow-slide)"/>
        
        <!-- Width Dimension Line -->
        <line x1="0" y1="-30" x2="{{WIDTH}}" y2="-30" stroke="#1b305b" stroke-width="3" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>
        <text x="{{CENTER_X}}" y="-40" font-family="Arial" font-size="28" font-weight="bold" text-anchor="middle" fill="#1b305b">{{WIDTH}} mm</text>
        
        <!-- Height Dimension Line -->
        <line x1="-30" y1="0" x2="-30" y2="{{HEIGHT}}" stroke="#1b305b" stroke-width="3" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>
        <text x="-40" y="{{CENTER_Y}}" font-family="Arial" font-size="28" font-weight="bold" text-anchor="middle" transform="rotate(-90 -40 {{CENTER_Y}})" fill="#1b305b">{{HEIGHT}} mm</text>
    </g>
</svg>
SVG;

        $casementSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="{{VB_X}} {{VB_Y}} {{VB_WIDTH}} {{VB_HEIGHT}}" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
    <defs>
        <marker id="arr-start-csm" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
            <path d="M5,0 L0,3 L5,6 z" fill="#1b305b" />
        </marker>
        <marker id="arr-end-csm" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
            <path d="M0,0 L5,3 L0,6 z" fill="#1b305b" />
        </marker>
    </defs>
    <g class="window-frame">
        <!-- Outer Frame -->
        <rect x="0" y="0" width="{{WIDTH}}" height="{{HEIGHT}}" fill="#f8fafc" stroke="#1e293b" stroke-width="36" rx="4"/>
        
        <!-- Sash Frame -->
        <rect x="36" y="36" width="{{INNER_WIDTH}}" height="{{INNER_HEIGHT}}" fill="#f0fdf4" stroke="#15803d" stroke-width="20" rx="2"/>
        
        <!-- Opening Swing Lines -->
        <line x1="36" y1="36" x2="{{INNER_WIDTH}}" y2="{{CENTER_Y}}" stroke="#15803d" stroke-width="4" stroke-dasharray="8,6"/>
        <line x1="36" y1="{{INNER_HEIGHT}}" x2="{{INNER_WIDTH}}" y2="{{CENTER_Y}}" stroke="#15803d" stroke-width="4" stroke-dasharray="8,6"/>
        
        <!-- Handle & Sash Tag -->
        <circle cx="{{INNER_WIDTH}} - 15" cy="{{CENTER_Y}}" r="12" fill="#1e293b"/>
        <text x="60" y="70" font-family="Arial" font-size="26" font-weight="bold" fill="#15803d">CSM-01</text>
        
        <!-- Dimensions -->
        <line x1="0" y1="-30" x2="{{WIDTH}}" y2="-30" stroke="#1b305b" stroke-width="3" marker-start="url(#arr-start-csm)" marker-end="url(#arr-end-csm)"/>
        <text x="{{CENTER_X}}" y="-40" font-family="Arial" font-size="28" font-weight="bold" text-anchor="middle" fill="#1b305b">{{WIDTH}} mm</text>
        
        <line x1="-30" y1="0" x2="-30" y2="{{HEIGHT}}" stroke="#1b305b" stroke-width="3" marker-start="url(#arr-start-csm)" marker-end="url(#arr-end-csm)"/>
        <text x="-40" y="{{CENTER_Y}}" font-family="Arial" font-size="28" font-weight="bold" text-anchor="middle" transform="rotate(-90 -40 {{CENTER_Y}})" fill="#1b305b">{{HEIGHT}} mm</text>
    </g>
</svg>
SVG;

        $fixedSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="{{VB_X}} {{VB_Y}} {{VB_WIDTH}} {{VB_HEIGHT}}" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
    <defs>
        <marker id="arr-start-fix" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
            <path d="M5,0 L0,3 L5,6 z" fill="#1b305b" />
        </marker>
        <marker id="arr-end-fix" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
            <path d="M0,0 L5,3 L0,6 z" fill="#1b305b" />
        </marker>
    </defs>
    <g class="window-frame">
        <!-- Outer Frame -->
        <rect x="0" y="0" width="{{WIDTH}}" height="{{HEIGHT}}" fill="#f8fafc" stroke="#1e293b" stroke-width="36" rx="4"/>
        
        <!-- Glass Area -->
        <rect x="36" y="36" width="{{INNER_WIDTH}}" height="{{INNER_HEIGHT}}" fill="#e0f2fe" stroke="#38bdf8" stroke-width="12" rx="2"/>
        <text x="{{CENTER_X}}" y="{{CENTER_Y}}" font-family="Arial" font-size="32" font-weight="bold" fill="#0284c7" text-anchor="middle" opacity="0.6">FIXED</text>
        
        <!-- Dimensions -->
        <line x1="0" y1="-30" x2="{{WIDTH}}" y2="-30" stroke="#1b305b" stroke-width="3" marker-start="url(#arr-start-fix)" marker-end="url(#arr-end-fix)"/>
        <text x="{{CENTER_X}}" y="-40" font-family="Arial" font-size="28" font-weight="bold" text-anchor="middle" fill="#1b305b">{{WIDTH}} mm</text>
        
        <line x1="-30" y1="0" x2="-30" y2="{{HEIGHT}}" stroke="#1b305b" stroke-width="3" marker-start="url(#arr-start-fix)" marker-end="url(#arr-end-fix)"/>
        <text x="-40" y="{{CENTER_Y}}" font-family="Arial" font-size="28" font-weight="bold" text-anchor="middle" transform="rotate(-90 -40 {{CENTER_Y}})" fill="#1b305b">{{HEIGHT}} mm</text>
    </g>
</svg>
SVG;

        Design::where('company_id', $companyId)->delete();

        $dSliding2T = Design::create([
            'company_id' => $companyId,
            'name' => '2-Track Sliding Window',
            'code' => 'SL-2T',
            'type' => 'Window',
            'svg_template' => $sliding2TrackSvg,
            'configuration' => ['panels' => 2, 'tracks' => 2, 'type' => 'Sliding']
        ]);

        $dSliding3T = Design::create([
            'company_id' => $companyId,
            'name' => '3-Track Sliding with Mesh',
            'code' => 'SL-3T-MSH',
            'type' => 'Window',
            'svg_template' => $sliding2TrackSvg,
            'configuration' => ['panels' => 3, 'tracks' => 3, 'type' => 'Sliding', 'mesh' => true]
        ]);

        $dCasement = Design::create([
            'company_id' => $companyId,
            'name' => 'Casement Openable Window',
            'code' => 'CSM-01',
            'type' => 'Window',
            'svg_template' => $casementSvg,
            'configuration' => ['panels' => 1, 'type' => 'Casement']
        ]);

        $dFixed = Design::create([
            'company_id' => $companyId,
            'name' => 'Fixed Panoramic Window',
            'code' => 'FIX-01',
            'type' => 'Window',
            'svg_template' => $fixedSvg,
            'configuration' => ['panels' => 1, 'type' => 'Fixed']
        ]);

        $dDoorSliding = Design::create([
            'company_id' => $companyId,
            'name' => '2-Track Sliding Door',
            'code' => 'DR-SLD-2T',
            'type' => 'Door',
            'svg_template' => $sliding2TrackSvg,
            'configuration' => ['panels' => 2, 'tracks' => 2, 'type' => 'Sliding Door']
        ]);

        // 5. Clean and Populate Real Production Products
        Product::where('company_id', $companyId)->delete();

        $prodList = [
            [
                'product_code' => 'PRD-SLD-2T',
                'name' => '2-Track uPVC Sliding Window',
                'category' => 'Sliding Window',
                'opening_type' => 'Sliding',
                'profile_brand' => 'CORA uPVC Systems',
                'profile_series' => '60mm Sliding Series',
                'glass_type' => '5mm Clear Toughened',
                'glass_thickness' => '5mm',
                'mesh_type' => 'No',
                'hardware_brand' => 'CORA Hardware',
                'base_rate' => 550.00,
                'labor_rate_per_sqft' => 45.00,
                'profit_margin_percent' => 20.00,
                'profile_calc_formula' => 'PERIMETER',
                'unit' => 'Sq.Ft.',
                'status' => 'Active',
                'company_id' => $companyId,
                'profile_details' => [
                    'System' => '60mm 2-Track Sliding System',
                    'Brand' => 'CORA uPVC Systems',
                    'Profile Color' => 'Standard White',
                    'Reinforcement' => '1.2mm Galvanized Steel Core',
                    'Weatherstripping' => 'EPDM Gasket & Double Wool Pile',
                ],
                'accessories_details' => [
                    'Rollers' => 'Heavy Duty Nylon Bearing Rollers',
                    'Locking' => 'Single Point Concealed Touch Lock',
                    'Fasteners' => 'Grade 304 Stainless Steel Screws',
                    'Drainage' => 'Self-draining Sill with Anti-backdraft Caps',
                ],
                'design_id' => $dSliding2T->id,
                'bom_rules' => [
                    ['material_id' => $matMap['PRF-UPVC-FRM60'], 'role' => 'Outer Frame', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-SLD-SSH'], 'role' => 'Sliding Sash', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-GB'], 'role' => 'Glazing Bead', 'formula' => '(w + h) * 2 / 1000 * 0.9', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['GLS-5MM-TGH'], 'role' => 'Glass', 'formula' => '(w * h / 1000000) * 10.764', 'type' => 'Formula', 'unit' => 'SqFt'],
                    ['material_id' => $matMap['HDW-SLD-RLR-SET'], 'role' => 'Rollers', 'formula' => '2', 'type' => 'FixedQty', 'unit' => 'Nos'],
                    ['material_id' => $matMap['HDW-TOUCH-LCK'], 'role' => 'Lock', 'formula' => '1', 'type' => 'FixedQty', 'unit' => 'Nos'],
                    ['material_id' => $matMap['ACC-EPDM-GSK'], 'role' => 'Gasket', 'formula' => '(w + h) * 2 / 1000 * 2', 'type' => 'Formula', 'unit' => 'Mtr'],
                ]
            ],
            [
                'product_code' => 'PRD-SLD-3TM',
                'name' => '3-Track uPVC Sliding Window with SS Mesh',
                'category' => 'Sliding Window',
                'opening_type' => 'Sliding with Mesh',
                'profile_brand' => 'CORA uPVC Systems',
                'profile_series' => '3-Track Sliding Series with Mesh',
                'glass_type' => '5mm Clear Toughened',
                'glass_thickness' => '5mm',
                'mesh_type' => 'SS Mesh (Stainless Steel)',
                'hardware_brand' => 'CORA Hardware',
                'base_rate' => 680.00,
                'labor_rate_per_sqft' => 50.00,
                'profit_margin_percent' => 20.00,
                'profile_calc_formula' => 'PERIMETER',
                'unit' => 'Sq.Ft.',
                'status' => 'Active',
                'company_id' => $companyId,
                'profile_details' => [
                    'System' => '3-Track Sliding with Integrated Flyscreen',
                    'Brand' => 'CORA uPVC Systems',
                    'Profile Color' => 'Standard White',
                    'Reinforcement' => '1.2mm Galvanized Steel Core',
                ],
                'accessories_details' => [
                    'Flyscreen' => 'SS 304 High Tensile Wire Mesh',
                    'Rollers' => 'Twin Heavy Duty Roller Sets',
                    'Locking' => 'Touch Lock with Key Override',
                ],
                'design_id' => $dSliding3T->id,
                'bom_rules' => [
                    ['material_id' => $matMap['PRF-UPVC-3T-FRM'], 'role' => '3-Track Frame', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-SLD-SSH'], 'role' => 'Glass Sash', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-MSH-SSH'], 'role' => 'Mesh Sash', 'formula' => '(w / 2 + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['GLS-5MM-TGH'], 'role' => 'Glass', 'formula' => '(w * h / 1000000) * 10.764', 'type' => 'Formula', 'unit' => 'SqFt'],
                    ['material_id' => $matMap['MSH-SS304'], 'role' => 'SS Mesh', 'formula' => '(w * h / 2 / 1000000) * 10.764', 'type' => 'Formula', 'unit' => 'SqFt'],
                    ['material_id' => $matMap['HDW-SLD-RLR-SET'], 'role' => 'Rollers', 'formula' => '3', 'type' => 'FixedQty', 'unit' => 'Nos'],
                    ['material_id' => $matMap['HDW-TOUCH-LCK'], 'role' => 'Lock', 'formula' => '2', 'type' => 'FixedQty', 'unit' => 'Nos'],
                ]
            ],
            [
                'product_code' => 'PRD-CSM-60',
                'name' => 'uPVC Casement Window',
                'category' => 'Casement Window',
                'opening_type' => 'Casement Outward',
                'profile_brand' => 'CORA uPVC Systems',
                'profile_series' => '60mm Casement Series',
                'glass_type' => '6mm Clear Toughened',
                'glass_thickness' => '6mm',
                'mesh_type' => 'No',
                'hardware_brand' => 'CORA Hardware',
                'base_rate' => 620.00,
                'labor_rate_per_sqft' => 45.00,
                'profit_margin_percent' => 20.00,
                'profile_calc_formula' => 'PERIMETER',
                'unit' => 'Sq.Ft.',
                'status' => 'Active',
                'company_id' => $companyId,
                'profile_details' => [
                    'System' => '60mm Outward Opening Casement',
                    'Brand' => 'CORA uPVC Systems',
                    'Profile Color' => 'Standard White',
                    'Reinforcement' => '1.5mm Heavy Galvanized Steel',
                    'Gaskets' => 'Dual Compression EPDM Gaskets',
                ],
                'accessories_details' => [
                    'Hinges' => 'SS 304 Heavy Duty Friction Stays (12 Inch)',
                    'Locking' => 'Multi-Point Espag Transmission Rod',
                    'Handle' => 'Ergonomic White Die-cast Cockspur Handle',
                ],
                'design_id' => $dCasement->id,
                'bom_rules' => [
                    ['material_id' => $matMap['PRF-UPVC-FRM60'], 'role' => 'Outer Frame', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-CSM-SSH'], 'role' => 'Casement Sash', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-GB'], 'role' => 'Glazing Bead', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['GLS-6MM-TGH'], 'role' => 'Glass', 'formula' => '(w * h / 1000000) * 10.764', 'type' => 'Formula', 'unit' => 'SqFt'],
                    ['material_id' => $matMap['HDW-FRIC-STAY'], 'role' => 'Friction Stays', 'formula' => '1', 'type' => 'FixedQty', 'unit' => 'Nos'],
                    ['material_id' => $matMap['HDW-ESPAG-SET'], 'role' => 'Espag Locking Set', 'formula' => '1', 'type' => 'FixedQty', 'unit' => 'Nos'],
                    ['material_id' => $matMap['ACC-EPDM-GSK'], 'role' => 'EPDM Gasket', 'formula' => '(w + h) * 2 / 1000 * 2', 'type' => 'Formula', 'unit' => 'Mtr'],
                ]
            ],
            [
                'product_code' => 'PRD-FIX-60',
                'name' => 'Fixed Panoramic Window',
                'category' => 'Fixed Window',
                'opening_type' => 'Fixed',
                'profile_brand' => 'CORA uPVC Systems',
                'profile_series' => '60mm Fixed Series',
                'glass_type' => '5mm Clear Toughened',
                'glass_thickness' => '5mm',
                'mesh_type' => 'No',
                'hardware_brand' => 'CORA Hardware',
                'base_rate' => 420.00,
                'labor_rate_per_sqft' => 30.00,
                'profit_margin_percent' => 20.00,
                'profile_calc_formula' => 'PERIMETER',
                'unit' => 'Sq.Ft.',
                'status' => 'Active',
                'company_id' => $companyId,
                'profile_details' => [
                    'System' => '60mm Fixed Glazing System',
                    'Brand' => 'CORA uPVC Systems',
                    'Profile Color' => 'Standard White',
                    'Reinforcement' => '1.2mm Galvanized Steel',
                ],
                'accessories_details' => [
                    'Bead' => 'Co-extruded Gasket Glazing Bead',
                    'Setting Blocks' => 'Neoprene Glazing Shims',
                ],
                'design_id' => $dFixed->id,
                'bom_rules' => [
                    ['material_id' => $matMap['PRF-UPVC-FRM60'], 'role' => 'Outer Frame', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-GB'], 'role' => 'Glazing Bead', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['GLS-5MM-TGH'], 'role' => 'Glass', 'formula' => '(w * h / 1000000) * 10.764', 'type' => 'Formula', 'unit' => 'SqFt'],
                    ['material_id' => $matMap['ACC-EPDM-GSK'], 'role' => 'EPDM Gasket', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                ]
            ],
            [
                'product_code' => 'PRD-DR-SLD',
                'name' => '2-Track uPVC Sliding Door',
                'category' => 'Sliding Door',
                'opening_type' => 'Sliding Door',
                'profile_brand' => 'CORA uPVC Systems',
                'profile_series' => 'Heavy Duty Sliding Door Series',
                'glass_type' => '8mm Clear Toughened',
                'glass_thickness' => '8mm',
                'mesh_type' => 'No',
                'hardware_brand' => 'CORA Hardware',
                'base_rate' => 750.00,
                'labor_rate_per_sqft' => 60.00,
                'profit_margin_percent' => 20.00,
                'profile_calc_formula' => 'PERIMETER',
                'unit' => 'Sq.Ft.',
                'status' => 'Active',
                'company_id' => $companyId,
                'profile_details' => [
                    'System' => 'Heavy Duty Patio Sliding Door',
                    'Brand' => 'CORA uPVC Systems',
                    'Profile Color' => 'Standard White',
                    'Reinforcement' => '2.0mm Heavy Duty Galvanized Steel Core',
                ],
                'accessories_details' => [
                    'Locking' => 'Heavy Duty D-Handle with Key Cylinder',
                    'Rollers' => 'Tandem Stainless Steel Bearing Rollers (150kg)',
                    'Interlock' => 'Reinforced Interlock with Weather Fin',
                ],
                'design_id' => $dDoorSliding->id,
                'bom_rules' => [
                    ['material_id' => $matMap['PRF-UPVC-FRM60'], 'role' => 'Outer Frame', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-SLD-SSH'], 'role' => 'Door Sash', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-UPVC-GB'], 'role' => 'Glazing Bead', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['GLS-8MM-TGH'], 'role' => 'Glass', 'formula' => '(w * h / 1000000) * 10.764', 'type' => 'Formula', 'unit' => 'SqFt'],
                    ['material_id' => $matMap['HDW-SLD-RLR-SET'], 'role' => 'Tandem Rollers', 'formula' => '2', 'type' => 'FixedQty', 'unit' => 'Nos'],
                    ['material_id' => $matMap['HDW-DR-HANDLE'], 'role' => 'Key Lock D-Handle', 'formula' => '1', 'type' => 'FixedQty', 'unit' => 'Nos'],
                ]
            ],
            [
                'product_code' => 'PRD-ALU-SLIM',
                'name' => 'Aluminium Slimline Casement Window',
                'category' => 'Aluminium Window',
                'opening_type' => 'Casement Outward',
                'profile_brand' => 'Jindal Aluminium',
                'profile_series' => '45mm Slimline Series',
                'glass_type' => '6mm Clear Toughened',
                'glass_thickness' => '6mm',
                'mesh_type' => 'No',
                'hardware_brand' => 'Caldwell Hardware',
                'base_rate' => 850.00,
                'labor_rate_per_sqft' => 65.00,
                'profit_margin_percent' => 20.00,
                'profile_calc_formula' => 'PERIMETER',
                'unit' => 'Sq.Ft.',
                'status' => 'Active',
                'company_id' => $companyId,
                'profile_details' => [
                    'System' => '45mm Slimline Thermal-Enhanced Aluminium',
                    'Brand' => 'Jindal Aluminium',
                    'Profile Color' => 'Powder Coated Anthracite Grey',
                    'Alloy' => '6063 T6 Architectural Grade',
                ],
                'accessories_details' => [
                    'Hinges' => 'Heavy Duty 4-Bar Friction Stays',
                    'Locking' => 'Flush Concealed Multi-Point Locking Handle',
                ],
                'design_id' => $dCasement->id,
                'bom_rules' => [
                    ['material_id' => $matMap['PRF-ALU-SLIM45'], 'role' => 'Alu Outer Frame', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['PRF-ALU-SLIM-SSH'], 'role' => 'Alu Sash', 'formula' => '(w + h) * 2 / 1000', 'type' => 'Formula', 'unit' => 'Mtr'],
                    ['material_id' => $matMap['GLS-6MM-TGH'], 'role' => 'Glass', 'formula' => '(w * h / 1000000) * 10.764', 'type' => 'Formula', 'unit' => 'SqFt'],
                    ['material_id' => $matMap['HDW-FRIC-STAY'], 'role' => 'Friction Stays', 'formula' => '1', 'type' => 'FixedQty', 'unit' => 'Nos'],
                    ['material_id' => $matMap['HDW-ESPAG-SET'], 'role' => 'Locking Set', 'formula' => '1', 'type' => 'FixedQty', 'unit' => 'Nos'],
                ]
            ],
        ];

        foreach ($prodList as $pData) {
            $bomList = $pData['bom_rules'];
            $designId = $pData['design_id'];
            unset($pData['bom_rules'], $pData['design_id']);

            $product = Product::create($pData);

            // Attach Design
            $product->designs()->syncWithoutDetaching([$designId]);

            // Create BOM Rules
            foreach ($bomList as $idx => $bRule) {
                BomRule::create([
                    'company_id' => $companyId,
                    'product_id' => $product->id,
                    'material_id' => $bRule['material_id'],
                    'component_role' => $bRule['role'],
                    'rule_type' => $bRule['type'],
                    'unit' => $bRule['unit'],
                    'formula' => $bRule['formula'],
                    'sort_order' => $idx + 1,
                ]);
            }
        }

        Schema::enableForeignKeyConstraints();
    }
}
