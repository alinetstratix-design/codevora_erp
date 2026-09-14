<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class BomRuleFactory extends Factory
{
    public function definition()
    {
        return [
            'component_role' => 'Frame',
            'rule_type' => 'Formula',
            'unit' => 'mm',
            'rule_definition' => [
                'operation' => 'PERIMETER',
                'inputs' => ['width', 'height'],
                'parameters' => ['width_multiplier' => 2, 'height_multiplier' => 2]
            ]
        ];
    }
}
