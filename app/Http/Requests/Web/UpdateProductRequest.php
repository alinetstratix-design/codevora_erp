<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'material' => 'nullable|string|max:100',
            'category' => 'nullable|string|max:100',
            'opening_type' => 'nullable|string|max:100',
            'profile_brand' => 'nullable|string|max:100',
            'profile_series' => 'nullable|string|max:100',
            'glass_type' => 'nullable|string|max:100',
            'glass_thickness' => 'nullable|string|max:100',
            'mesh_type' => 'nullable|string|max:100',
            'hardware_brand' => 'nullable|string|max:100',
            'uom' => 'nullable|string|max:50',
            'unit' => 'nullable|string|max:50',
            'base_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_weight_per_mtr' => 'nullable|numeric|min:0',
            'profile_rate_per_kg' => 'nullable|numeric|min:0',
            'glass_rate_per_sqft' => 'nullable|numeric|min:0',
            'hardware_kit_cost' => 'nullable|numeric|min:0',
            'wastage_percent' => 'nullable|numeric|min:0',
            'profit_margin_percent' => 'nullable|numeric|min:0',
            'labor_rate_per_sqft' => 'nullable|numeric|min:0',
            'profile_calc_formula' => 'nullable|string|in:PERIMETER,FIXED_MULTIPLIER',
            'profile_details' => 'nullable|array',
            'profile_details.*' => 'nullable|string|max:255',
            'accessories_details' => 'nullable|array',
            'accessories_details.*' => 'nullable|string|max:255',
        ];
    }
}
