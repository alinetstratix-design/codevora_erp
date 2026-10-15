<?php

namespace App\DTOs;

class BankDTO
{
    public array $bankDetails;

    public function __construct(array $bankDetails = [])
    {
        $this->bankDetails = $bankDetails; // company-owned; never filled with defaults
    }

    public function toArray(): array
    {
        return [
            'bank_details' => $this->bankDetails,
        ];
    }
}
