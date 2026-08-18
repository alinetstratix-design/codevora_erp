<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // We can add authorization logic here later
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'project_name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'date' => 'required|date',
            'quote_no' => 'required|string|max:255',
            'status' => ['nullable', 'string', \Illuminate\Validation\Rule::in(\App\Constants\QuotationStatus::all())],
            'valid_till' => 'nullable|date',
            'opportunity_no' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:1000',
            'subtotal' => 'nullable|numeric|min:0',
            'discount_percent' => 'nullable|numeric|min:0|max:100',
            'tax_percent' => 'nullable|numeric|min:0|max:100',
            'tax_amount' => 'nullable|numeric|min:0',
            'additional_charges' => 'nullable|numeric|min:0',
            'grand_total' => 'nullable|numeric|min:0',
            'installation_cost_rate' => 'nullable|numeric|min:0',
            'freight_charges_rate' => 'nullable|numeric|min:0',
            'terms_conditions' => 'nullable|array',
            'bank_details' => 'nullable|array',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|exists:products,id',
            'items.*.position' => 'required|string|max:255',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.system_name' => 'nullable|string|max:255',
            'items.*.dimension_w' => 'required|numeric|min:1',
            'items.*.dimension_h' => 'required|numeric|min:1',
            'items.*.profile_color' => 'nullable|string|max:255',
            'items.*.handle_type' => 'nullable|string|max:255',
            'items.*.hardware_color' => 'nullable|string|max:255',
            'items.*.mesh_type' => 'nullable|string|max:255',
            'items.*.glass_type' => 'nullable|string|max:255',
            'items.*.remarks' => 'nullable|string|max:1000',
            'items.*.drawing_type' => 'nullable|string|max:50',
            'items.*.drawing_url' => 'nullable|string|max:255',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.rate' => 'nullable|numeric|min:0',
            'items.*.material_cost' => 'nullable|numeric|min:0',
            'items.*.glass_cost' => 'nullable|numeric|min:0',
            'items.*.total_cost' => 'nullable|numeric|min:0',
            'items.*.technical_specs' => 'nullable|array',
            'items.*.item_code' => 'nullable|string|max:50',
            'items.*.profile_system' => 'nullable|string|max:255',
            'items.*.weight_kg' => 'nullable|numeric|min:0',
            'items.*.value_per_sqft' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'nullable|numeric|min:0',
            'items.*.profile_details' => 'nullable|array',
            'items.*.accessories_details' => 'nullable|array',
            'items.*.drawing_metadata' => 'nullable|array',
        ];
    }
}
