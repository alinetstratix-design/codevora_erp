<?php

namespace App\DTOs;

class BankDTO
{
    public array $bankDetails;

    public function __construct(array $bankDetails = [])
    {
        $this->bankDetails = !empty($bankDetails) ? $bankDetails : [
            '50% Advance with Order',
            '50% Before Delivery / Dispatch'
        ];
    }

    public function toArray(): array
    {
        return [
            'bank_details' => $this->bankDetails,
        ];
    }
}
