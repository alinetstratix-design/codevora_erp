<?php

namespace Tests\Unit\Calc;

use App\Services\BOM\RuleEngine;
use App\Services\BOM\SafeMathEvaluator;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class SafeMathEvaluatorTest extends TestCase
{
    public function test_respects_operator_precedence_and_parentheses(): void
    {
        $this->assertEquals(14.0, SafeMathEvaluator::evaluate('2 + 3 * 4'));
        $this->assertEquals(20.0, SafeMathEvaluator::evaluate('(2 + 3) * 4'));
        $this->assertEquals(-5.0, SafeMathEvaluator::evaluate('-(2 + 3)'));
        $this->assertEquals(0.5, SafeMathEvaluator::evaluate('1 / 2'));
    }

    public function test_rejects_division_by_zero_and_bad_syntax(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeMathEvaluator::evaluate('1 / 0');
    }

    public function test_rejects_code_injection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        SafeMathEvaluator::evaluate('system("id")');
    }

    public function test_formula_string_uses_context_variables(): void
    {
        $context = ['width' => 1200, 'height' => 1500, 'quantity' => 2];
        $this->assertEquals(5.4, RuleEngine::evaluateFormulaString('(w + h) * 2 / 1000', $context));
    }

    public function test_unknown_formula_variable_is_an_error_not_zero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RuleEngine::evaluateFormulaString('(w + panel_count) * 2', ['width' => 1000, 'height' => 1000]);
    }
}
