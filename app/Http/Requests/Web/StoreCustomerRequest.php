<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|unique:customers,phone',
            'email' => 'nullable|email|max:255|unique:customers,email',
            'gst_number' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|in:Active,Inactive',
        ];
    }

    public function messages()
    {
        return [
            'phone.unique' => 'A customer with this phone number already exists in the system.',
            'email.unique' => 'A customer with this email address already exists in the system.',
        ];
    }
}
