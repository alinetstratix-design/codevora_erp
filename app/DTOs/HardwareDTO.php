<?php

namespace App\DTOs;

class HardwareDTO
{
    public string $brand;
    public string $color;

    public function __construct(array $data = [])
    {
        $this->brand = $data['hardware_brand'] ?? 'CORA Hardware';
        $this->color = $data['hardware_color'] ?? 'WHITE';
    }

    public function toArray(): array
    {
        return [
            'brand' => $this->brand,
            'color' => $this->color,
        ];
    }
}
