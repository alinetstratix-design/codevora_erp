<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Design;
use App\Models\Product;
use Illuminate\Http\Request;

class DesignController extends Controller
{
    /**
     * Get all active designs.
     */
    public function index()
    {
        $designs = Design::where('is_active', true)->get();
        return response()->json($designs);
    }

    /**
     * Get designs attached to a specific product.
     */
    public function getByProduct($productId)
    {
        $product = Product::findOrFail($productId);
        $designs = $product->designs()->where('is_active', true)->get();
        return response()->json($designs);
    }

    /**
     * Get specific design details including SVG template.
     */
    public function show(Design $design)
    {
        return response()->json($design);
    }
}
