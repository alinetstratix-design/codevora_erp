<?php

namespace App\DTOs;

class ValidationItemDTO
{
    public string $ruleKey;
    public string $label;
    public string $group;
    public bool $passed;
    public string $message;
    public int $weight;

    public function __construct(string $ruleKey, string $label, string $group, bool $passed, string $message = '', int $weight = 1)
    {
        $this->ruleKey = $ruleKey;
        $this->label = $label;
        $this->group = $group;
        $this->passed = $passed;
        $this->message = $passed ? "{$label} is valid" : ($message ?: "{$label} is missing or invalid");
        $this->weight = $weight;
    }

    public function toArray(): array
    {
        return [
            'rule_key' => $this->ruleKey,
            'label' => $this->label,
            'group' => $this->group,
            'passed' => $this->passed,
            'message' => $this->message,
            'weight' => $this->weight,
        ];
    }
}
