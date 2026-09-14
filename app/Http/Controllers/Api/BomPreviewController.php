<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\BOM\BomEngine;
use Illuminate\Http\Request;

class BomPreviewController extends Controller
{
    /**
     * POST /api/products/{product}/bom-preview
     * 
     * Payload:
     * {
     *   "width": 1200,
     *   "height": 1500,
     *   "quantity": 1,
     *   "panel_count": 2,
     *   ...
     * }
     */
    public function preview(Request $request, Product $product, BomEngine $bomEngine, \App\Services\BOM\CostingEngine $costingEngine)
    {
        // Enforce tenant isolation
        if ($product->company_id !== auth()->user()->company_id) {
            abort(403, 'Unauthorized access to this product.');
        }

        $context = $request->validate([
            'width' => 'numeric|nullable',
            'height' => 'numeric|nullable',
            'quantity' => 'numeric|nullable',
        ]);
        
        // Merge any other unstructured context inputs (like panel_count) safely
        $context = array_merge($request->except(['_token']), $context);

        $bomResult = $bomEngine->calculate($product, $context);
        $costResult = $costingEngine->calculate($bomResult);

        return response()->json([
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
            'context' => $context,
            'components' => $product->components()->select('id', 'component_role')->get(),
            'bom' => $bomResult['bom'],
            'cost' => $costResult,
            'explanations' => $bomResult['explanations']
        ]);
    }
}
