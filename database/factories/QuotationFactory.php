<?php

namespace Database\Factories;

use App\Models\Quotation;
use App\Models\Customer;
use App\Models\CompanySetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuotationFactory extends Factory
{
    protected $model = Quotation::class;

    public function definition()
    {
        return [
            'quotation_number' => 'QT-' . $this->faker->unique()->numberBetween(1000, 9999),
            'quotation_date' => now()->format('Y-m-d'),
            'valid_until' => now()->addDays(30)->format('Y-m-d'),
            'customer_id' => Customer::factory(),
            'company_setting_id' => CompanySetting::factory(),
            'client_name' => $this->faker->name,
            'project_name' => $this->faker->company . ' Project',
            'project_location' => $this->faker->city,
            'sales_person' => $this->faker->name,
            'remarks' => $this->faker->sentence,
            'status' => 'Draft',
            'bom_cost_total' => 0.00,
            'additional_cost_total' => 0.00,
            'cost_basis_total' => 0.00,
            'margin_total' => 0.00,
            'subtotal' => 0.00,
            'discount' => 0.00,
            'taxable_amount' => 0.00,
            'tax_percent' => 18.00,
            'tax_amount' => 0.00,
            'grand_total' => 0.00,
        ];
    }
}
