<?php

namespace App\Http\Controllers;

use App\Models\Product;

class ProductController extends CrudController
{
    protected $modelClass = Product::class;
    protected $resourceClass = \App\Http\Resources\ProductResource::class;

    protected function storeRules(): array
    {
        return [
            'product_code' => 'required|string|unique:products',
            'name' => 'required|string',
            'material' => 'nullable|string',
            'category' => 'nullable|string',
            'opening_type' => 'nullable|string',
            'profile_brand' => 'nullable|string',
            'profile_series' => 'nullable|string',
            'glass_type' => 'nullable|string',
            'glass_thickness' => 'nullable|string',
            'mesh_type' => 'nullable|string',
            'hardware_brand' => 'nullable|string',
            'default_image' => 'nullable|string',
            'uom' => 'nullable|string',
            'unit' => 'nullable|string',
            'base_rate' => 'numeric',
            'description' => 'nullable|string',
            'status' => 'string',
            'profile_details' => 'nullable|array',
            'accessories_details' => 'nullable|array',
        ];
    }

    protected function updateRules($id): array
    {
        return [
            'product_code' => 'sometimes|required|string|unique:products,product_code,'.$id,
            'name' => 'sometimes|required|string',
            'material' => 'nullable|string',
            'category' => 'nullable|string',
            'opening_type' => 'nullable|string',
            'profile_brand' => 'nullable|string',
            'profile_series' => 'nullable|string',
            'glass_type' => 'nullable|string',
            'glass_thickness' => 'nullable|string',
            'mesh_type' => 'nullable|string',
            'hardware_brand' => 'nullable|string',
            'default_image' => 'nullable|string',
            'uom' => 'nullable|string',
            'unit' => 'nullable|string',
            'base_rate' => 'numeric',
            'description' => 'nullable|string',
            'status' => 'string',
            'profile_details' => 'nullable|array',
            'accessories_details' => 'nullable|array',
        ];
    }
}
