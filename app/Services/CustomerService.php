<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerService
{
    public function createCustomer(array $data)
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['customer_code'])) {
                $maxId = Customer::max('id') + 1;
                $data['customer_code'] = 'CUST-' . str_pad($maxId, 5, '0', STR_PAD_LEFT);
            }
            return Customer::create($data);
        });
    }

    public function updateCustomer(Customer $customer, array $data)
    {
        return DB::transaction(function () use ($customer, $data) {
            $customer->update($data);
            return $customer;
        });
    }

    public function deleteCustomer(Customer $customer)
    {
        return DB::transaction(function () use ($customer) {
            return $customer->delete();
        });
    }
}
