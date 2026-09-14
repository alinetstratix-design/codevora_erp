<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Design;
use App\Models\Company;

class DesignSvgSeeder extends Seeder
{
    public function run()
    {
        $companyId = \App\Models\Company::first()->id ?? null;

        $fixedSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{WIDTH}} {{HEIGHT}}" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
    <g class="window-frame">
        <!-- Outer Frame -->
        <rect x="0" y="0" width="{{WIDTH}}" height="{{HEIGHT}}" fill="none" stroke="#2c3e50" stroke-width="40"/>
        <!-- Glass -->
        <rect x="40" y="40" width="{{INNER_WIDTH}}" height="{{INNER_HEIGHT}}" fill="#e0f7fa" stroke="#bdc3c7" stroke-width="10"/>
        
        <!-- Dimensions -->
        <text x="{{CENTER_X}}" y="-20" font-family="Arial" font-size="40" text-anchor="middle" fill="#333">{{WIDTH}} mm</text>
        <text x="-20" y="{{CENTER_Y}}" font-family="Arial" font-size="40" text-anchor="middle" transform="rotate(-90 -20 {{CENTER_Y}})" fill="#333">{{HEIGHT}} mm</text>
    </g>
</svg>
SVG;

        $sliding2TrackSvg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {{WIDTH}} {{HEIGHT}}" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
    <g class="window-frame">
        <!-- Outer Frame -->
        <rect x="0" y="0" width="{{WIDTH}}" height="{{HEIGHT}}" fill="none" stroke="#2c3e50" stroke-width="40"/>
        
        <!-- Left Panel -->
        <rect x="40" y="40" width="{{PANEL_WIDTH}}" height="{{INNER_HEIGHT}}" fill="#e0f7fa" stroke="#34495e" stroke-width="20"/>
        <!-- Right Panel (Sliding) -->
        <rect x="{{PANEL_2_X}}" y="40" width="{{PANEL_WIDTH}}" height="{{INNER_HEIGHT}}" fill="#b2ebf2" stroke="#34495e" stroke-width="20"/>
        
        <!-- Arrow indicating sliding -->
        <path d="M {{CENTER_X}} {{CENTER_Y}} L {{SLIDE_ARROW_END}} {{CENTER_Y}}" stroke="#e74c3c" stroke-width="10" marker-end="url(#arrow)"/>
        
        <!-- Dimensions -->
        <text x="{{CENTER_X}}" y="-20" font-family="Arial" font-size="40" text-anchor="middle" fill="#333">{{WIDTH}} mm</text>
        <text x="-20" y="{{CENTER_Y}}" font-family="Arial" font-size="40" text-anchor="middle" transform="rotate(-90 -20 {{CENTER_Y}})" fill="#333">{{HEIGHT}} mm</text>
    </g>
    <defs>
        <marker id="arrow" viewBox="0 0 10 10" refX="5" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
            <path d="M 0 0 L 10 5 L 0 10 z" fill="#e74c3c" />
        </marker>
    </defs>
</svg>
SVG;

        $design1 = Design::updateOrCreate(
            ['code' => 'FW-01', 'company_id' => $companyId],
            [
                'name' => 'Fixed Window',
                'type' => 'Window',
                'svg_template' => $fixedSvg,
                'preview_image' => 'images/designs/fixed_window.jpg',
                'configuration' => [
                    'panels' => 1,
                    'type' => 'Fixed'
                ]
            ]
        );

        $design2 = Design::updateOrCreate(
            ['code' => 'SL-2T', 'company_id' => $companyId],
            [
                'name' => '2 Track Sliding Window',
                'type' => 'Window',
                'svg_template' => $sliding2TrackSvg,
                'preview_image' => 'images/designs/sliding_window.jpg',
                'configuration' => [
                    'panels' => 2,
                    'tracks' => 2,
                    'type' => 'Sliding'
                ]
            ]
        );

        // Attach designs to all existing products
        $products = \App\Models\Product::all();
        foreach ($products as $product) {
            $product->designs()->syncWithoutDetaching([$design1->id, $design2->id]);
        }
    }
}
