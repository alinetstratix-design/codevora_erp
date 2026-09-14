<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\BOM\RuleEngine;
use InvalidArgumentException;

class RuleEngineTest extends TestCase
{
    public function test_evaluates_condition_equals()
    {
        $context = ['track_count' => 2];
        
        $condition = ['field' => 'track_count', 'operator' => 'EQUALS', 'value' => 2];
        $this->assertTrue(RuleEngine::evaluateCondition($condition, $context));

        $condition = ['field' => 'track_count', 'operator' => 'EQUALS', 'value' => 3];
        $this->assertFalse(RuleEngine::evaluateCondition($condition, $context));
    }

    public function test_evaluates_condition_greater_than()
    {
        $context = ['height' => 1900];
        
        $condition = ['field' => 'height', 'operator' => 'GREATER_THAN', 'value' => 1800];
        $this->assertTrue(RuleEngine::evaluateCondition($condition, $context));

        $condition = ['field' => 'height', 'operator' => 'GREATER_THAN', 'value' => 2000];
        $this->assertFalse(RuleEngine::evaluateCondition($condition, $context));
    }

    public function test_evaluates_rule_fixed_value()
    {
        $rule = ['operation' => 'FIXED', 'value' => 10.5];
        $result = RuleEngine::evaluateRule($rule, []);
        $this->assertEquals(10.5, $result);
    }

    public function test_evaluates_rule_variable()
    {
        $context = ['panel_count' => 4];
        $rule = ['operation' => 'VARIABLE', 'field' => 'panel_count'];
        
        $result = RuleEngine::evaluateRule($rule, $context);
        $this->assertEquals(4.0, $result);
    }

    public function test_evaluates_rule_math_operations()
    {
        $context = ['width' => 1000, 'panels' => 2];
        
        // (width / panels) + 50
        $rule = [
            'operation' => 'ADD',
            'left' => [
                'operation' => 'DIVIDE',
                'left' => 'width', // test variable fallback shorthand
                'right' => 'panels'
            ],
            'right' => 50
        ];
        
        $result = RuleEngine::evaluateRule($rule, $context);
        $this->assertEquals(550.0, $result);
    }

    public function test_evaluates_rule_perimeter()
    {
        $context = ['width' => 1200, 'height' => 1500];
        
        $rule = [
            'operation' => 'PERIMETER',
            'inputs' => ['width', 'height'],
            'parameters' => ['width_multiplier' => 2, 'height_multiplier' => 2]
        ];
        
        $result = RuleEngine::evaluateRule($rule, $context);
        $this->assertEquals(5400.0, $result); // 1200*2 + 1500*2 = 5400
    }

    public function test_evaluates_rule_area()
    {
        $context = ['width' => 1200, 'height' => 1500];
        
        $rule = [
            'operation' => 'AREA',
            'inputs' => ['width', 'height'],
            'parameters' => ['width_deduction' => 120, 'height_deduction' => 120]
        ];
        
        $result = RuleEngine::evaluateRule($rule, $context);
        $this->assertEquals(1490400.0, $result); // (1200-120)*(1500-120) = 1080 * 1380
    }

    public function test_protects_against_infinite_recursion()
    {
        $this->expectException(\App\Exceptions\RuleDepthExceededException::class);
        
        $infiniteRule = [
            'operation' => 'ADD',
            'left' => 1,
        ];
        // Create circular reference
        $infiniteRule['right'] = &$infiniteRule;

        RuleEngine::evaluateRule($infiniteRule, []);
    }
}
