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

        $defaults = $isSliding ? [
            'Profile Color' => 'WHITE',
            'MeshType' => '(3) SS FLYMESH',
            'Flymesh Sash' => 'Sld Sash 39Mm X 57Mm',
            'Flymesh Sash Ri' => 'Ri-32.6Mm X 13Mm X 24.6Mm X 15.4Mm-1.5Mm',
            'Guide Rail' => 'Al Guide Rail(Cora-Gr)',
            'Interlock' => 'Sld Interlock 43Mm X 40Mm (Crs-40Wsil)',
            'Sliding Sash' => 'Sld Sash 39Mm X 57Mm',
            'Sliding Sash Ri' => 'Ri-32.6Mm X 13Mm X 24.6Mm X 15.4Mm-1.5Mm',
            'Track' => 'Sld 3Track 108Mm X 50Mm',
            'Track Ri' => 'Ri-13Mm X 28Mm-1Mm',
        ] : [
            'Profile Color' => 'WHITE',
            'MeshType' => 'No',
            'Casement Sash' => 'Cmt Outward Sash 60Mm X 104Mm',
            'Casement Sash Ri' => 'Ri-34Mm X 49Mm X 48Mm X 10Mm-1.5Mm',
            'Outer' => 'Cmt Outer 60Mm X 60Mm',
            'Outer Ri' => 'Ri-27Mm X 22Mm-1.5Mm',
            'Door Panel' => 'Door Panel 100Mm X 15Mm',
            'Sash Mullion' => 'Cmt T-Mullion 60Mm X 77Mm (Crc-60Tm)',
            'Mullion Ri' => 'Ri-18Mm X 30Mm(Cc 60Tm Rf-1.5)',
        ];

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
