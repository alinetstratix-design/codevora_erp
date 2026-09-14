<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialFactory extends Factory
{
    public function definition()
    {
        return [
            'sku' => $this->faker->unique()->lexify('MAT-????'),
            'name' => $this->faker->word . ' Profile',
            'category' => 'Profile',
            'uom' => 'm',
            'cost' => $this->faker->randomFloat(2, 50, 500),
            'waste_percent' => 5,
            'is_active' => true,
        ];
    }
}
