<?php

namespace App\Services;

class HardwareRuleEngine
{
    /**
     * Automatically evaluate and select hardware set based on opening type, dimensions, and weight.
     */
    public function selectHardware(string $openingType, float $width, float $height, float $weightKg, array $overrides = []): array
    {
        $type = strtolower($openingType);
        $w = max(100, $width);
        $h = max(100, $height);
        $wt = max(1, $weightKg);

        if (str_contains($type, 'sliding')) {
            $locking = $overrides['locking'] ?? ($w > 1200 ? 'Multi-point' : 'Single-point');
            $handleType = $overrides['handle_type'] ?? ($w > 1200 ? 'S1-Sld Touch Lock Left & Right' : 'S3-Sld C Handle');
            $roller = $overrides['roller'] ?? ($wt > 45 ? 'Heavy Double Wheel Roller' : 'Single Wheel With Groove');
            $friction = 'NA';
            $hinge = 'NA';
            $armRestrictor = 'NA';
            $cylinder = 'NA';
        } elseif (str_contains($type, 'fixed')) {
            $locking = 'NA';
            $handleType = 'NA';
            $roller = 'NA';
            $friction = 'NA';
            $hinge = 'NA';
            $armRestrictor = 'NA';
            $cylinder = 'NA';
        } else {
            // Casement / Tilt & Turn / Doors
            $locking = $overrides['locking'] ?? ($h > 1800 ? 'Multi-point' : 'Single-point');
            $handleType = $overrides['handle_type'] ?? ($h > 1800 ? 'S1-Casement Double Side Door Handle With Key' : 'S1-Espag Handle Without Key');
            $hinge = $overrides['hinge'] ?? ($h > 1800 ? 'S1-3D Hinges (3 Pcs)' : 'S1-2D Hinges (2 Pcs)');
            
            // Friction stay sizing based on width/height
            if ($w <= 600) {
                $friction = $overrides['friction'] ?? 'S1-Friction Stay 12"';
            } elseif ($w <= 900) {
                $friction = $overrides['friction'] ?? 'S1-Friction Stay 18"';
            } else {
                $friction = $overrides['friction'] ?? 'S1-Friction Stay 24"';
            }

            $armRestrictor = $overrides['arm_restrictor'] ?? ($h > 1800 ? 'Restrictor Arm 10"' : 'NA');
            $cylinder = $overrides['cylinder'] ?? ($h > 1800 ? 'Brass Euro Profile Cylinder' : 'NA');
            $roller = 'NA';
        }

        return [
            'Locking' => $locking,
            'Handle color' => $overrides['handle_color'] ?? 'WHITE',
            'Arm Restrictor' => $armRestrictor,
            'Cylinder' => $cylinder,
            'Handle Type' => $handleType,
            'Hinge' => $hinge,
            'Friction' => $friction,
            'Roller' => $roller,
            'Flymesh Handle Type' => $overrides['flymesh_handle_type'] ?? (str_contains($type, 'mesh') ? 'S3-Sld C Handle' : 'NA')
        ];
    }
}
