<?php

namespace App\Services\BOM;

use InvalidArgumentException;

/**
 * Evaluates arithmetic expressions (numbers, + - * / and parentheses) without eval().
 */
class SafeMathEvaluator
{
    /** @var array<int, array{0:string,1:float|string}> */
    private array $tokens = [];
    private int $pos = 0;

    public static function evaluate(string $expression): float
    {
        $evaluator = new self();
        $evaluator->tokens = self::tokenize($expression);

        if (empty($evaluator->tokens)) {
            throw new InvalidArgumentException('Empty formula expression.');
        }

        $value = $evaluator->parseExpression();

        if ($evaluator->pos < count($evaluator->tokens)) {
            throw new InvalidArgumentException('Unexpected token in formula expression.');
        }

        if (!is_finite($value)) {
            throw new InvalidArgumentException('Formula result is not a finite number.');
        }

        return $value;
    }

    /**
     * @return array<int, array{0:string,1:float|string}>
     */
    private static function tokenize(string $expression): array
    {
        $tokens = [];
        $length = strlen($expression);
        $i = 0;

        while ($i < $length) {
            $char = $expression[$i];

            if (ctype_space($char)) {
                $i++;
                continue;
            }

            if (ctype_digit($char) || $char === '.') {
                if (!preg_match('/\G(\d+\.?\d*|\.\d+)([eE][+-]?\d+)?/', $expression, $matches, 0, $i)) {
                    throw new InvalidArgumentException('Invalid number in formula expression.');
                }
                $tokens[] = ['num', (float) $matches[0]];
                $i += strlen($matches[0]);
                continue;
            }

            if (strpos('+-*/()', $char) !== false) {
                $tokens[] = ['op', $char];
                $i++;
                continue;
            }

            throw new InvalidArgumentException("Invalid character [{$char}] in formula expression.");
        }

        return $tokens;
    }

    private function peek(): ?array
    {
        return $this->tokens[$this->pos] ?? null;
    }

    private function parseExpression(): float
    {
        $value = $this->parseTerm();

        while (($token = $this->peek()) !== null && $token[0] === 'op' && ($token[1] === '+' || $token[1] === '-')) {
            $this->pos++;
            $right = $this->parseTerm();
            $value = $token[1] === '+' ? $value + $right : $value - $right;
        }

        return $value;
    }

    private function parseTerm(): float
    {
        $value = $this->parseFactor();

        while (($token = $this->peek()) !== null && $token[0] === 'op' && ($token[1] === '*' || $token[1] === '/')) {
            $this->pos++;
            $right = $this->parseFactor();

            if ($token[1] === '/') {
                if ($right == 0.0) {
                    throw new InvalidArgumentException('Division by zero in formula expression.');
                }
                $value = $value / $right;
            } else {
                $value = $value * $right;
            }
        }

        return $value;
    }

    private function parseFactor(): float
    {
        $token = $this->peek();

        if ($token === null) {
            throw new InvalidArgumentException('Incomplete formula expression.');
        }

        if ($token[0] === 'op' && $token[1] === '-') {
            $this->pos++;
            return -$this->parseFactor();
        }

        if ($token[0] === 'op' && $token[1] === '+') {
            $this->pos++;
            return $this->parseFactor();
        }

        if ($token[0] === 'op' && $token[1] === '(') {
            $this->pos++;
            $value = $this->parseExpression();
            $close = $this->peek();

            if ($close === null || $close[0] !== 'op' || $close[1] !== ')') {
                throw new InvalidArgumentException('Missing closing parenthesis in formula expression.');
            }

            $this->pos++;
            return $value;
        }

        if ($token[0] === 'num') {
            $this->pos++;
            return (float) $token[1];
        }

        throw new InvalidArgumentException('Invalid syntax in formula expression.');
    }
}
