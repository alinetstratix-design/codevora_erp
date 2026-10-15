<?php

namespace App\DTOs;

class ProfileDTO
{
    public string $color;
    public string $meshType;
    public string $casementSash;
    public string $casementSashRi;
    public string $outer;
    public string $outerRi;
    public string $doorPanel;
    public string $sashMullion;
    public string $mullionRi;
    public array $details;

    public function __construct(array $data = [], string $systemName = '')
    {
        $isSliding = stripos($systemName, 'SLIDING') !== false;

        $defaults = ['Profile Color' => 'WHITE', 'MeshType' => 'No']; // profile dimensions come from the product

        // Merge user provided data over defaults
        $merged = array_merge($defaults, is_array($data) ? array_filter($data, fn($v) => !is_null($v) && trim($v) !== '') : []);

        $this->color = $merged['Profile Color'] ?? $merged['color'] ?? 'WHITE';
        $this->meshType = $merged['MeshType'] ?? $merged['mesh_type'] ?? 'No';
        $this->casementSash = $merged['Casement Sash'] ?? '';
        $this->casementSashRi = $merged['Casement Sash Ri'] ?? '';
        $this->outer = $merged['Outer'] ?? '';
        $this->outerRi = $merged['Outer Ri'] ?? '';
        $this->doorPanel = $merged['Door Panel'] ?? '';
        $this->sashMullion = $merged['Sash Mullion'] ?? '';
        $this->mullionRi = $merged['Mullion Ri'] ?? '';
        
        $this->details = $merged;
    }

    public function toArray(): array
    {
        return [
            'color' => $this->color,
            'mesh_type' => $this->meshType,
            'casement_sash' => $this->casementSash,
            'casement_sash_ri' => $this->casementSashRi,
            'outer' => $this->outer,
            'outer_ri' => $this->outerRi,
            'door_panel' => $this->doorPanel,
            'sash_mullion' => $this->sashMullion,
            'mullion_ri' => $this->mullionRi,
            'details' => $this->details,
        ];
    }
}
