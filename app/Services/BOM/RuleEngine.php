<?php

namespace App\Services\BOM;

use InvalidArgumentException;

class RuleEngine
{
    /**
     * Evaluate a structured condition.
     * Example: {"field": "track_count", "operator": "EQUALS", "value": 2}
     * Returns true if there is no condition or if condition matches.
     */
    public static function evaluateCondition(?array $condition, array $context): bool
    {
        if (empty($condition)) {
            return true;
        }

        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'EQUALS';
        $targetValue = $condition['value'] ?? null;

        if (!$field) {
            return true;
        }

        $contextValue = $context[$field] ?? null;

        return match (strtoupper($operator)) {
            'EQUALS' => $contextValue == $targetValue,
            'NOT_EQUALS' => $contextValue != $targetValue,
            'GREATER_THAN' => $contextValue > $targetValue,
            'LESS_THAN' => $contextValue < $targetValue,
            'GREATER_THAN_OR_EQUAL' => $contextValue >= $targetValue,
            'LESS_THAN_OR_EQUAL' => $contextValue <= $targetValue,
            'IN' => is_array($targetValue) && in_array($contextValue, $targetValue),
            'NOT_IN' => is_array($targetValue) && !in_array($contextValue, $targetValue),
            default => throw new InvalidArgumentException("Unknown condition operator: {$operator}"),
        };
    }

    /**
     * Evaluate a structured rule AST.
     * Example: {"operation": "PERIMETER", "inputs": ["width", "height"], "parameters": {"width_multiplier": 2, "height_multiplier": 2}}
     * Or: {"operation": "MULTIPLY", "left": "panel_count", "right": 2}
     *
     * Unknown fields and invalid formulas throw; they never silently become 0.
     */
    public static function evaluateRule(array|int|float|string|null $rule, array $context, int $depth = 0): float
    {
        if ($depth > 50) {
            throw new \App\Exceptions\RuleDepthExceededException("Rule evaluation depth exceeded 50.");
        }

        if (is_numeric($rule)) {
            return (float) $rule;
        }

        if (is_string($rule)) {
            if (array_key_exists($rule, $context)) {
                return (float) $context[$rule];
            }
            return self::evaluateFormulaString($rule, $context);
        }

        if (empty($rule) || !is_array($rule)) {
            return 0.0;
        }

        $operation = strtoupper($rule['operation'] ?? 'FIXED');

        return match ($operation) {
            'FIXED' => (float) self::resolveValue($rule['value'] ?? 0, $context),
            'VARIABLE' => (float) self::resolveValue($rule['field'] ?? '', $context),

            'ADD' => self::evaluateRule($rule['left'] ?? 0, $context, $depth + 1) + self::evaluateRule($rule['right'] ?? 0, $context, $depth + 1),
            'SUBTRACT' => self::evaluateRule($rule['left'] ?? 0, $context, $depth + 1) - self::evaluateRule($rule['right'] ?? 0, $context, $depth + 1),
            'MULTIPLY' => self::evaluateRule($rule['left'] ?? 0, $context, $depth + 1) * self::evaluateRule($rule['right'] ?? 0, $context, $depth + 1),
            'DIVIDE' => self::divideSafe(
                self::evaluateRule($rule['left'] ?? 0, $context, $depth + 1),
                self::evaluateRule($rule['right'] ?? 1, $context, $depth + 1)
            ),

            'MIN' => min(
                self::evaluateRule($rule['left'] ?? 0, $context, $depth + 1),
                self::evaluateRule($rule['right'] ?? 0, $context, $depth + 1)
            ),
            'MAX' => max(
                self::evaluateRule($rule['left'] ?? 0, $context, $depth + 1),
                self::evaluateRule($rule['right'] ?? 0, $context, $depth + 1)
            ),

            'PERIMETER' => self::calculatePerimeter($rule, $context),
            'AREA' => self::calculateArea($rule, $context),

            default => throw new InvalidArgumentException("Unknown rule operation: {$operation}"),
        };
    }

    private static function calculatePerimeter(array $rule, array $context): float
    {
        $inputs = $rule['inputs'] ?? ['width', 'height'];
        $w = (float) self::resolveValue($inputs[0] ?? 'width', $context);
        $h = (float) self::resolveValue($inputs[1] ?? 'height', $context);

        $wMult = (float) ($rule['parameters']['width_multiplier'] ?? 2);
        $hMult = (float) ($rule['parameters']['height_multiplier'] ?? 2);

        return ($w * $wMult) + ($h * $hMult);
    }

    private static function calculateArea(array $rule, array $context): float
    {
        $inputs = $rule['inputs'] ?? ['width', 'height'];
        $w = (float) self::resolveValue($inputs[0] ?? 'width', $context);
        $h = (float) self::resolveValue($inputs[1] ?? 'height', $context);

        $wDed = (float) ($rule['parameters']['width_deduction'] ?? 0);
        $hDed = (float) ($rule['parameters']['height_deduction'] ?? 0);

        return max(0, $w - $wDed) * max(0, $h - $hDed);
    }

    /**
     * Resolves a literal number or a context field name. Unknown names are an error.
     */
    private static function resolveValue(mixed $value, array $context): mixed
    {
        if (is_numeric($value)) {
            return $value;
        }

        if ($value === null || $value === '') {
            return 0;
        }

        if (is_string($value)) {
            if (array_key_exists($value, $context)) {
                return $context[$value];
            }
            throw new InvalidArgumentException("Unknown field [{$value}] referenced in rule.");
        }

        throw new InvalidArgumentException('Invalid value in rule definition.');
    }

    private static function divideSafe(float $left, float $right): float
    {
        if ($right == 0) {
            throw new InvalidArgumentException("Division by zero in rule evaluation.");
        }
        return $left / $right;
    }

    /**
     * Evaluate a formula string with context variable substitution.
     * Supported variables: w/width, h/height, qty/quantity and any numeric context key.
     * Example: "(w + h) * 2 / 1000"
     */
    public static function evaluateFormulaString(string $formula, array $context): float
    {
        $formula = trim($formula);
        if ($formula === '') {
            return 0.0;
        }

        if (is_numeric($formula)) {
            return (float) $formula;
        }

        $w = (float) ($context['width'] ?? 0);
        $h = (float) ($context['height'] ?? 0);
        $qty = (float) ($context['quantity'] ?? 1);

        $expr = preg_replace_callback('/\b([a-zA-Z_][a-zA-Z0-9_]*)\b/', function ($matches) use ($context, $w, $h, $qty, $formula) {
            $name = $matches[1];
            $var = strtolower($name);

            if ($var === 'w' || $var === 'width') {
                return self::numberLiteral($w);
            }
            if ($var === 'h' || $var === 'height') {
                return self::numberLiteral($h);
            }
            if ($var === 'qty' || $var === 'quantity') {
                return self::numberLiteral($qty);
            }
            if (array_key_exists($name, $context) && is_numeric($context[$name])) {
                return self::numberLiteral((float) $context[$name]);
            }

            throw new InvalidArgumentException("Unknown variable [{$name}] in formula [{$formula}].");
        }, $formula);

        return SafeMathEvaluator::evaluate($expr);
    }

    private static function numberLiteral(float $value): string
    {
        return number_format($value, 10, '.', '');
    }
}
