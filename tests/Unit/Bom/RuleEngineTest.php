<?php

namespace Tests\Unit\Bom;

use PHPUnit\Framework\TestCase;
use App\Services\BOM\RuleEngine;

class RuleEngineTest extends TestCase
{
    public function test_evaluates_basic_math()
    {
        $rule = [
            'operation' => 'ADD',
            'left' => 10,
            'right' => 5
        ];
        $this->assertEquals(15, RuleEngine::evaluateRule($rule, []));

        $rule2 = [
            'operation' => 'MULTIPLY',
            'left' => 10,
            'right' => ['operation' => 'VARIABLE', 'field' => 'qty']
        ];
        $this->assertEquals(30, RuleEngine::evaluateRule($rule2, ['qty' => 3]));
    }

    public function test_evaluates_perimeter()
    {
        $rule = [
            'operation' => 'PERIMETER',
            'inputs' => ['width', 'height'],
            'parameters' => ['width_multiplier' => 2, 'height_multiplier' => 2]
        ];

        $context = ['width' => 1000, 'height' => 1500];
        
        $this->assertEquals(5000, RuleEngine::evaluateRule($rule, $context));
    }

    public function test_evaluates_conditions()
    {
        $condition = [
            'field' => 'track_count',
            'operator' => 'EQUALS',
            'value' => 2
        ];

        $this->assertTrue(RuleEngine::evaluateCondition($condition, ['track_count' => 2]));
        $this->assertFalse(RuleEngine::evaluateCondition($condition, ['track_count' => 3]));
    }

    public function test_recursion_depth_limit_throws_exception()
    {
        $this->expectException(\App\Exceptions\RuleDepthExceededException::class);

        $rule = ['operation' => 'ADD', 'left' => 1, 'right' => 1];
        
        // Build a nested rule 51 levels deep
        for ($i = 0; $i < 51; $i++) {
            $rule = ['operation' => 'ADD', 'left' => $rule, 'right' => 1];
        }

        RuleEngine::evaluateRule($rule, []);
    }
}
