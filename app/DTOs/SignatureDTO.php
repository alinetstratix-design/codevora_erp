<?php

namespace App\DTOs;

class SignatureDTO
{
    public string $leftLabel;
    public string $rightLabel;
    public string $signatureImage;

    public function __construct(string $leftLabel = 'Authorized Signatory', string $rightLabel = 'Signature of Customer', string $signatureImage = '')
    {
        $this->leftLabel = $leftLabel;
        $this->rightLabel = $rightLabel;
        $this->signatureImage = $signatureImage;
    }

    public function toArray(): array
    {
        return [
            'left_label' => $this->leftLabel,
            'right_label' => $this->rightLabel,
            'signature_image' => $this->signatureImage,
        ];
    }
}
