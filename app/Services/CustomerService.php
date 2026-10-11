<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function createCustomer(array $data, int $companyId)
    {
        return DB::transaction(function () use ($data, $companyId) {
            $data['company_id'] = $companyId;

            if (empty($data['customer_code'])) {
                $data['customer_code'] = $this->nextCustomerCode($companyId);
            }

            return Customer::create($data);
        });
    }

    public function updateCustomer(Customer $customer, array $data)
    {
        return DB::transaction(function () use ($customer, $data) {
            unset($data['company_id'], $data['customer_code']);
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

    /**
     * Next free code CUST-00001 within the company (soft-deleted customers count too).
     */
    private function nextCustomerCode(int $companyId): string
    {
        $query = fn () => Customer::withTrashed()->withoutGlobalScopes()->where('company_id', $companyId);

        $number = $query()->count() + 1;
        do {
            $code = 'CUST-' . str_pad((string) $number, 5, '0', STR_PAD_LEFT);
            $number++;
        } while ($query()->where('customer_code', $code)->exists());

        return $code;
    }
}
