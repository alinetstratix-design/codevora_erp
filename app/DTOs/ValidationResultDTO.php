<?php

namespace App\DTOs;

class ValidationResultDTO
{
    public int $scorePercent;
    public string $badgeColor; // green (success), yellow (warning), red (danger)
    public string $statusLabel;
    public bool $canApprove;
    public array $groupScores;
    public array $passedChecks;
    public array $failedChecks;
    public array $allItems;

    public function __construct(int $scorePercent, array $allItems = [], array $groupScores = [])
    {
        $this->scorePercent = max(0, min(100, $scorePercent));
        
        if ($this->scorePercent === 100) {
            $this->badgeColor = 'success';
            $this->statusLabel = 'Ready For Approval';
            $this->canApprove = true;
        } elseif ($this->scorePercent >= 70) {
            $this->badgeColor = 'warning';
            $this->statusLabel = 'Pending Validation';
            $this->canApprove = false;
        } else {
            $this->badgeColor = 'danger';
            $this->statusLabel = 'Incomplete Draft';
            $this->canApprove = false;
        }

        $this->allItems = $allItems;
        $this->groupScores = $groupScores;

        $this->passedChecks = array_filter($allItems, fn($i) => $i instanceof ValidationItemDTO ? $i->passed : $i['passed']);
        $this->failedChecks = array_filter($allItems, fn($i) => !($i instanceof ValidationItemDTO ? $i->passed : $i['passed']));
    }

    public function toArray(): array
    {
        return [
            'score_percent' => $this->scorePercent,
            'badge_color' => $this->badgeColor,
            'status_label' => $this->statusLabel,
            'can_approve' => $this->canApprove,
            'group_scores' => $this->groupScores,
            'passed_count' => count($this->passedChecks),
            'failed_count' => count($this->failedChecks),
            'total_count' => count($this->allItems),
            'failed_messages' => array_values(array_map(fn($i) => $i instanceof ValidationItemDTO ? $i->message : $i['message'], $this->failedChecks)),
            'all_items' => array_map(fn($i) => $i instanceof ValidationItemDTO ? $i->toArray() : $i, $this->allItems),
        ];
    }
}
