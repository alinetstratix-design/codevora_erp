<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition()
    {
        return [
            'customer_code' => 'CUST-' . $this->faker->unique()->numberBetween(100, 999),
            'name' => $this->faker->name,
            'company_name' => $this->faker->company,
            'type' => 'Retail',
            'contact_person' => $this->faker->name,
            'phone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'company_id' => Company::factory(),
            'status' => 'Active',
        ];
    }
}
