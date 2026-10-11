<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Validation\Rule;

class CustomerController extends CrudController
{
    protected $modelClass = Customer::class;
    protected $resourceClass = \App\Http\Resources\CustomerResource::class;

    protected function storeRules(): array
    {
        return [
            'customer_code' => ['required', 'string', Rule::unique('customers', 'customer_code')->where('company_id', auth()->user()->company_id)],
            'name' => 'required|string',
            'type' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'location' => 'nullable|string',
            'status' => 'string'
        ];
    }

    protected function updateRules($id): array
    {
        return [
            'customer_code' => ['sometimes', 'required', 'string', Rule::unique('customers', 'customer_code')->ignore($id)->where('company_id', auth()->user()->company_id)],
            'name' => 'sometimes|required|string',
            'type' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'location' => 'nullable|string',
            'status' => 'string'
        ];
    }

    protected function preStore(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;
        return $data;
    }
}
