<?php

namespace App\Http\Controllers;

use App\Models\Customer;

class CustomerController extends CrudController
{
    protected $modelClass = Customer::class;
    protected $resourceClass = \App\Http\Resources\CustomerResource::class;

    protected function storeRules(): array
    {
        return [
            'customer_code' => 'required|string|unique:customers',
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
            'customer_code' => 'sometimes|required|string|unique:customers,customer_code,'.$id,
            'name' => 'sometimes|required|string',
            'type' => 'nullable|string',
            'contact_person' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'location' => 'nullable|string',
            'status' => 'string'
        ];
    }
}
