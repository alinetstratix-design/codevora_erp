<?php

namespace App\DTOs;

class GlassDTO
{
    public string $type;
    public string $thickness;

    public function __construct(array $data = [])
    {
        $this->type = $data['glass_type'] ?? '(1) 5mm Clear Toughened';
        $this->thickness = $data['glass_thickness'] ?? '5mm';
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type,
            'thickness' => $this->thickness,
        ];
    }
}
