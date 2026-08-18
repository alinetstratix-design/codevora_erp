<?php

namespace App\DTOs;

class AccessoryDTO
{
    public string $locking;
    public string $handleColor;
    public string $armRestrictor;
    public string $cylinder;
    public string $handleType;
    public string $hinge;
    public string $friction;
    public string $roller;
    public string $flymeshHandleType;
    public array $details;

    public function __construct(array $data = [], string $systemName = '')
    {
        $isSliding = stripos($systemName, 'SLIDING') !== false;

        $defaults = $isSliding ? [
            'Locking' => 'Single-point',
            'Handle color' => 'WHITE',
            'Flymesh Handle Type' => 'S3-Sld C Handle',
            'Handle Type' => 'S1-Sld Touch Lock Left',
            'Roller' => 'Single Wheel With Groove',
        ] : [
            'Locking' => 'Multi-point',
            'Handle color' => 'WHITE',
            'Arm Restrictor' => 'Restrictor Arm 10"',
            'Cylinder' => 'Cylinder',
            'Handle Type' => 'S1-Casement Double Side Door Handle With Key',
            'Hinge' => 'S1-3D Hinges',
        ];

        $merged = array_merge($defaults, is_array($data) ? array_filter($data, fn($v) => !is_null($v) && trim($v) !== '') : []);

        $this->locking = $merged['Locking'] ?? $merged['locking'] ?? '';
        $this->handleColor = $merged['Handle color'] ?? $merged['handle_color'] ?? 'WHITE';
        $this->armRestrictor = $merged['Arm Restrictor'] ?? $merged['arm_restrictor'] ?? '';
        $this->cylinder = $merged['Cylinder'] ?? $merged['cylinder'] ?? '';
        $this->handleType = $merged['Handle Type'] ?? $merged['handle_type'] ?? '';
        $this->hinge = $merged['Hinge'] ?? $merged['hinge'] ?? '';
        $this->friction = $merged['Friction'] ?? $merged['friction'] ?? '';
        $this->roller = $merged['Roller'] ?? $merged['roller'] ?? '';
        $this->flymeshHandleType = $merged['Flymesh Handle Type'] ?? $merged['flymesh_handle_type'] ?? '';

        $this->details = $merged;
    }

    public function toArray(): array
    {
        return [
            'locking' => $this->locking,
            'handle_color' => $this->handleColor,
            'arm_restrictor' => $this->armRestrictor,
            'cylinder' => $this->cylinder,
            'handle_type' => $this->handleType,
            'hinge' => $this->hinge,
            'friction' => $this->friction,
            'roller' => $this->roller,
            'flymesh_handle_type' => $this->flymeshHandleType,
            'details' => $this->details,
        ];
    }
}
