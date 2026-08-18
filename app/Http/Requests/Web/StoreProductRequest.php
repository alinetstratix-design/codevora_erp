<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'opening_type' => 'nullable|string|max:100',
            'profile_brand' => 'nullable|string|max:100',
            'profile_series' => 'nullable|string|max:100',
            'glass_type' => 'nullable|string|max:100',
            'glass_thickness' => 'nullable|string|max:100',
            'mesh_type' => 'nullable|string|max:100',
            'hardware_brand' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'base_rate' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }
}
