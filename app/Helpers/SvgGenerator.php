<?php

namespace App\Helpers;

class SvgGenerator
{
    /**
     * Generate an SVG string for a window/door technical drawing matching EvA PDF diagrams.
     */
    public static function generateWindowDrawing($widthVal, $heightVal, $unit = 'mm', $type = 'Casement Series', $metadata = [])
    {
        $w = (float)($widthVal > 0 ? $widthVal : 1000);
        $h = (float)($heightVal > 0 ? $heightVal : 1000);

        $svgWidth = 240;
        $svgHeight = 240;

        $marginLeft = 35;
        $marginTop = 15;
        $marginRight = 35;
        $marginBottom = 35;

        $drawWidth = $svgWidth - $marginLeft - $marginRight;
        $drawHeight = $svgHeight - $marginTop - $marginBottom;

        $aspect = $w / $h;

        if ($aspect > 1) {
            $boxWidth = $drawWidth;
            $boxHeight = max(40, $boxWidth / $aspect);
        } else {
            $boxHeight = $drawHeight;
            $boxWidth = max(40, $boxHeight * $aspect);
        }

        $x = $marginLeft + ($drawWidth - $boxWidth) / 2;
        $y = $marginTop + ($drawHeight - $boxHeight) / 2;

        $cleanUnit = strtolower(trim($unit ?: 'mm'));
        $wMm = \App\Services\BOM\UnitConverter::convert($w, $cleanUnit, 'mm');
        $hMm = \App\Services\BOM\UnitConverter::convert($h, $cleanUnit, 'mm');

        $isFan = (bool)($metadata['fan_cutout'] ?? (stripos($type, 'fan') !== false || ($wMm < 700 && $hMm < 700 && stripos($type, 'casement') !== false)));
        $isSliding = (bool)(stripos($type, 'sliding') !== false || (isset($metadata['tracks']) && $metadata['tracks'] > 1));

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$svgWidth.' '.$svgHeight.'" width="100%" height="100%" style="background:#fff;">';

        // Defs for arrows
        $svg .= '
        <defs>
            <marker id="arr-start" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                <path d="M5,0 L0,3 L5,6 z" fill="#333" />
            </marker>
            <marker id="arr-end" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                <path d="M0,0 L5,3 L0,6 z" fill="#333" />
            </marker>
        </defs>';

        // Outer Frame
        $svg .= '<rect x="'.$x.'" y="'.$y.'" width="'.$boxWidth.'" height="'.$boxHeight.'" fill="#eaf2f8" stroke="#1f618d" stroke-width="2"/>';
        // Inner Frame
        $svg .= '<rect x="'.($x+3).'" y="'.($y+3).'" width="'.($boxWidth-6).'" height="'.($boxHeight-6).'" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';

        if ($isFan) {
            // Fan Cutout Window Diagram (EvA style W6, W26, W33, W34, W35, W38, W46)
            $fanRadius = min($boxWidth, $boxHeight) * 0.28;
            $centerX = $x + ($boxWidth / 2);
            $centerY = $y + ($boxHeight / 2);

            // FAN Tag at top left inside frame
            $svg .= '<rect x="'.($x+5).'" y="'.($y+5).'" width="32" height="14" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';
            $svg .= '<text x="'.($x+21).'" y="'.($y+15).'" font-family="sans-serif" font-size="9" font-weight="bold" fill="#000" text-anchor="middle">FAN</text>';

            // Fan Outer Circle
            $svg .= '<circle cx="'.$centerX.'" cy="'.$centerY.'" r="'.$fanRadius.'" fill="#c6e2ff" stroke="#1f618d" stroke-width="1.5"/>';
            // Fan Inner Circle
            $svg .= '<circle cx="'.$centerX.'" cy="'.$centerY.'" r="'.($fanRadius * 0.45).'" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';

            // Fan Blades (4 blades)
            for ($angle = 0; $angle < 360; $angle += 90) {
                $rad = deg2rad($angle);
                $bx1 = $centerX + ($fanRadius * 0.45) * cos($rad);
                $by1 = $centerY + ($fanRadius * 0.45) * sin($rad);
                $bx2 = $centerX + ($fanRadius * 0.9) * cos($rad + 0.3);
                $by2 = $centerY + ($fanRadius * 0.9) * sin($rad + 0.3);
                $svg .= '<line x1="'.$bx1.'" y1="'.$by1.'" x2="'.$bx2.'" y2="'.$by2.'" stroke="#1f618d" stroke-width="1.5"/>';
            }

            // Glass Circle Number 1
            $svg .= '<circle cx="'.$centerX.'" cy="'.$centerY.'" r="8" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';
            $svg .= '<text x="'.$centerX.'" y="'.($centerY+3).'" font-family="sans-serif" font-size="9" font-weight="bold" fill="#000" text-anchor="middle">1</text>';

            // F1 Badge at bottom right
            $svg .= '<rect x="'.($x+$boxWidth-22).'" y="'.($y+$boxHeight-18).'" width="16" height="12" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';
            $svg .= '<text x="'.($x+$boxWidth-14).'" y="'.($y+$boxHeight-9).'" font-family="sans-serif" font-size="8" font-weight="bold" fill="#000" text-anchor="middle">F1</text>';

        } elseif ($isSliding) {
            // 2-Track or 3-Track Sliding Window Diagram
            $tracks = (int)($metadata['tracks'] ?? 2);

            // S1 Tag top left
            $svg .= '<rect x="'.($x+5).'" y="'.($y+5).'" width="20" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
            $svg .= '<text x="'.($x+15).'" y="'.($y+12).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">S1</text>';

            // S2 Tag top right
            $svg .= '<rect x="'.($x+$boxWidth-25).'" y="'.($y+5).'" width="20" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
            $svg .= '<text x="'.($x+$boxWidth-15).'" y="'.($y+12).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">S2</text>';

            if ($tracks == 3) {
                $w3 = ($boxWidth - 8) / 3;
                $svg .= '<rect x="'.($x+4).'" y="'.($y+4).'" width="'.$w3.'" height="'.($boxHeight-8).'" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';
                $svg .= '<rect x="'.($x+4+$w3).'" y="'.($y+4).'" width="'.$w3.'" height="'.($boxHeight-8).'" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';
                $svg .= '<rect x="'.($x+4+($w3*2)).'" y="'.($y+4).'" width="'.$w3.'" height="'.($boxHeight-8).'" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';
            } else {
                $halfW = ($boxWidth - 8) / 2;
                $svg .= '<rect x="'.($x+4).'" y="'.($y+4).'" width="'.$halfW.'" height="'.($boxHeight-8).'" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';
                $svg .= '<rect x="'.($x+4+$halfW).'" y="'.($y+4).'" width="'.$halfW.'" height="'.($boxHeight-8).'" fill="#ffffff" stroke="#1f618d" stroke-width="1"/>';

                // Glass Tag 1 & 2
                $svg .= '<circle cx="'.($x+4+($halfW/2)).'" cy="'.($y+($boxHeight/2)).'" r="7" fill="#fff" stroke="#1f618d" stroke-width="1"/>';
                $svg .= '<text x="'.($x+4+($halfW/2)).'" y="'.($y+($boxHeight/2)+3).'" font-family="sans-serif" font-size="8" fill="#000" text-anchor="middle">1</text>';

                $svg .= '<circle cx="'.($x+4+$halfW+($halfW/2)).'" cy="'.($y+($boxHeight/2)).'" r="7" fill="#fff" stroke="#1f618d" stroke-width="1"/>';
                $svg .= '<text x="'.($x+4+$halfW+($halfW/2)).'" y="'.($y+($boxHeight/2)+3).'" font-family="sans-serif" font-size="8" fill="#000" text-anchor="middle">2</text>';
            }

            // GHH height indicator
            $ghhY = $y + ($boxHeight * 0.55);
            $svg .= '<line x1="'.($x+6).'" y1="'.$ghhY.'" x2="'.($x+$boxWidth-6).'" y2="'.$ghhY.'" stroke="#666" stroke-dasharray="2,2" stroke-width="0.8"/>';
            $svg .= '<rect x="'.($x+($boxWidth/2)-20).'" y="'.($ghhY-6).'" width="40" height="10" fill="#ffffff" stroke="#666" stroke-width="0.5"/>';
            $svg .= '<text x="'.($x+($boxWidth/2)).'" y="'.($ghhY+2).'" font-family="sans-serif" font-size="6" fill="#333" text-anchor="middle">GHH = 561</text>';

            // MS3 & F1 Badges
            $svg .= '<rect x="'.($x+$boxWidth-22).'" y="'.($y+$boxHeight-16).'" width="16" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
            $svg .= '<text x="'.($x+$boxWidth-14).'" y="'.($y+$boxHeight-8).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">F1</text>';

            $svg .= '<rect x="'.($x+$boxWidth-38).'" y="'.($y+$boxHeight-28).'" width="16" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
            $svg .= '<text x="'.($x+$boxWidth-30).'" y="'.($y+$boxHeight-20).'" font-family="sans-serif" font-size="6" fill="#000" text-anchor="middle">TN</text>';

        } else {
            // Single / Double Casement Window or Door Diagram
            $isDouble = (bool)($wMm >= 1100);

            // S1 Tag top left
            $svg .= '<rect x="'.($x+5).'" y="'.($y+5).'" width="20" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
            $svg .= '<text x="'.($x+15).'" y="'.($y+12).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">S1</text>';

            if ($isDouble) {
                // S2 Tag top right
                $svg .= '<rect x="'.($x+$boxWidth-25).'" y="'.($y+5).'" width="20" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
                $svg .= '<text x="'.($x+$boxWidth-15).'" y="'.($y+12).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">S2</text>';

                $midX = $x + ($boxWidth / 2);
                // Vertical Mullion Line
                $svg .= '<line x1="'.$midX.'" y1="'.$y.'" x2="'.$midX.'" y2="'.($y+$boxHeight).'" stroke="#1f618d" stroke-width="2"/>';

                // D tags for doors
                $svg .= '<rect x="'.($x+($boxWidth*0.25)-6).'" y="'.($y+($boxHeight*0.25)).'" width="12" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
                $svg .= '<text x="'.($x+($boxWidth*0.25)).'" y="'.($y+($boxHeight*0.25)+7).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">D</text>';

                $svg .= '<rect x="'.($x+($boxWidth*0.75)-6).'" y="'.($y+($boxHeight*0.25)).'" width="12" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
                $svg .= '<text x="'.($x+($boxWidth*0.75)).'" y="'.($y+($boxHeight*0.25)+7).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">D</text>';

            } else {
                // Single Panel D tag
                $svg .= '<rect x="'.($x+($boxWidth/2)-6).'" y="'.($y+($boxHeight*0.25)).'" width="12" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
                $svg .= '<text x="'.($x+($boxWidth/2)).'" y="'.($y+($boxHeight*0.25)+7).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">D</text>';

                // Glass Tag 1 if height is large
                $svg .= '<circle cx="'.($x+($boxWidth/2)).'" cy="'.($y+($boxHeight*0.12)).'" r="6" fill="#fff" stroke="#1f618d" stroke-width="0.8"/>';
                $svg .= '<text x="'.($x+($boxWidth/2)).'" y="'.($y+($boxHeight*0.12)+3).'" font-family="sans-serif" font-size="7" fill="#000" text-anchor="middle">1</text>';
            }

            // GHH handle height indicator
            $ghhY = $y + ($boxHeight * 0.52);
            $svg .= '<line x1="'.($x+6).'" y1="'.$ghhY.'" x2="'.($x+$boxWidth-6).'" y2="'.$ghhY.'" stroke="#666" stroke-dasharray="2,2" stroke-width="0.8"/>';

            $svg .= '<rect x="'.($x+($boxWidth/2)-22).'" y="'.($ghhY-5).'" width="44" height="10" fill="#ffffff" stroke="#666" stroke-width="0.5"/>';
            $svg .= '<text x="'.($x+($boxWidth/2)).'" y="'.($ghhY+3).'" font-family="sans-serif" font-size="6" fill="#333" text-anchor="middle">GHH = 1001</text>';

            // OUT Badges
            $svg .= '<rect x="'.($x+($boxWidth*0.25)-12).'" y="'.($ghhY+8).'" width="24" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
            $svg .= '<text x="'.($x+($boxWidth*0.25)).'" y="'.($ghhY+15).'" font-family="sans-serif" font-size="6" fill="#000" text-anchor="middle">OUT</text>';

            if ($isDouble) {
                $svg .= '<rect x="'.($x+($boxWidth*0.75)-12).'" y="'.($ghhY+8).'" width="24" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
                $svg .= '<text x="'.($x+($boxWidth*0.75)).'" y="'.($ghhY+15).'" font-family="sans-serif" font-size="6" fill="#000" text-anchor="middle">OUT</text>';
            }

            // F1 Badge bottom right
            $svg .= '<rect x="'.($x+$boxWidth-20).'" y="'.($y+$boxHeight-16).'" width="14" height="10" fill="#ffffff" stroke="#1f618d" stroke-width="0.8"/>';
            $svg .= '<text x="'.($x+$boxWidth-13).'" y="'.($y+$boxHeight-8).'" font-family="sans-serif" font-size="6" fill="#000" text-anchor="middle">F1</text>';
        }

        $unitLabel = ($cleanUnit === 'inch' || $cleanUnit === 'in') ? ' in' : ($cleanUnit === 'ft' ? ' ft' : ($cleanUnit === 'cm' ? ' cm' : ' mm'));

        // Width Dimension Arrow (Bottom)
        $arrowY = $y + $boxHeight + 10;
        $svg .= '<line x1="'.$x.'" y1="'.$arrowY.'" x2="'.($x+$boxWidth).'" y2="'.$arrowY.'" stroke="#333" stroke-width="1" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>';
        $svg .= '<text x="'.($x+($boxWidth/2)).'" y="'.($arrowY+12).'" font-family="sans-serif" font-size="9" font-weight="bold" fill="#000" text-anchor="middle">'.number_format($w, 2, '.', '').$unitLabel.'</text>';

        // Height Dimension Arrow (Left)
        $arrowX = $x - 10;
        $svg .= '<line x1="'.$arrowX.'" y1="'.$y.'" x2="'.$arrowX.'" y2="'.($y+$boxHeight).'" stroke="#333" stroke-width="1" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>';
        $svg .= '<text x="'.($arrowX-4).'" y="'.($y+($boxHeight/2)).'" font-family="sans-serif" font-size="9" font-weight="bold" fill="#000" text-anchor="middle" transform="rotate(-90, '.($arrowX-4).', '.($y+($boxHeight/2)).')">'.number_format($h, 2, '.', '').$unitLabel.'</text>';

        $svg .= '</svg>';

        return $svg;
    }
}
