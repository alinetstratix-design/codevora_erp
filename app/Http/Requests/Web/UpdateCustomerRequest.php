<?php

namespace App\Http\Requests\Web;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $customerId = $this->route('customer') ? $this->route('customer')->id : null;

        return [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', Rule::unique('customers', 'phone')->ignore($customerId)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)],
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
