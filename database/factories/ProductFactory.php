<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition()
    {
        return [
            'product_code' => $this->faker->unique()->lexify('PRD-????'),
            'name' => $this->faker->word . ' Window',
            'category' => 'Sliding',
            'status' => 'Active',
            'base_rate' => $this->faker->randomFloat(2, 1000, 5000),
        ];
    }
}
