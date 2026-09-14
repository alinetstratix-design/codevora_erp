<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CompanySettingFactory extends Factory
{
    public function definition()
    {
        return [
            'company_name' => $this->faker->company,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }
}
