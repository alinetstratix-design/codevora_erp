<?php

namespace App\Helpers;

class SvgGenerator
{
    /**
     * Generate an SVG string for a window/door technical drawing matching EvA CAD elevation diagrams.
     */
    public static function generateWindowDrawing($widthVal, $heightVal, $unit = 'mm', $type = 'Casement Series', $metadata = [])
    {
        $w = (float)($widthVal > 0 ? $widthVal : 1000);
        $h = (float)($heightVal > 0 ? $heightVal : 1000);

        $svgWidth = 240;
        $svgHeight = 285;

        $marginLeft = 36;
        $marginTop = 12;
        $marginRight = 24;
        $marginBottom = 65; // Reserved for intermediate dimensions, total dimension, and track view

        $drawWidth = $svgWidth - $marginLeft - $marginRight;
        $drawHeight = $svgHeight - $marginTop - $marginBottom;

        $aspect = $w / $h;

        if ($aspect > 1) {
            $boxWidth = $drawWidth;
            $boxHeight = max(55, min($drawHeight, $boxWidth / $aspect));
        } else {
            $boxHeight = $drawHeight;
            $boxWidth = max(55, min($drawWidth, $boxHeight * $aspect));
        }

        $x = $marginLeft + ($drawWidth - $boxWidth) / 2;
        $y = $marginTop + ($drawHeight - $boxHeight) / 2;

        $cleanUnit = strtolower(trim($unit ?: 'mm'));
        $wMm = \App\Services\BOM\UnitConverter::convert($w, $cleanUnit, 'mm');
        $hMm = \App\Services\BOM\UnitConverter::convert($h, $cleanUnit, 'mm');

        $unitLabel = ($cleanUnit === 'inch' || $cleanUnit === 'in') ? ' in' : ($cleanUnit === 'ft' ? ' ft' : ($cleanUnit === 'cm' ? ' cm' : ' mm'));

        $isFan = (bool)($metadata['fan_cutout'] ?? (stripos($type, 'fan') !== false || ($wMm < 700 && $hMm < 700 && stripos($type, 'casement') !== false)));
        $isSliding = (bool)(stripos($type, 'sliding') !== false || (isset($metadata['tracks']) && $metadata['tracks'] > 1));

        $hasMesh = false;
        if (isset($metadata['has_mesh'])) {
            $hasMesh = (bool)$metadata['has_mesh'];
        } elseif (!empty($metadata['mesh_type'])) {
            $hasMesh = !in_array(strtolower(trim($metadata['mesh_type'])), ['no', 'none', 'no mesh', 'no_mesh']);
        } elseif (stripos($type, 'mesh') !== false) {
            $hasMesh = true;
        }

        $frameThick = 5;
        $sashThick = 4;

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$svgWidth.' '.$svgHeight.'" width="100%" height="100%" style="background:#ffffff;">';

        // Defs for arrows and markers
        $svg .= '
        <defs>
            <marker id="arr-start" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                <path d="M5,0 L0,3 L5,6 z" fill="#333333" />
            </marker>
            <marker id="arr-end" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                <path d="M0,0 L5,3 L0,6 z" fill="#333333" />
            </marker>
            <marker id="slide-arrow" markerWidth="6" markerHeight="6" refX="3" refY="3" orient="auto">
                <path d="M0,0 L5,3 L0,6 z" fill="#1b4f72" />
            </marker>
        </defs>';

        // --- 1. OUTER WINDOW FRAME ---
        $svg .= '<rect x="'.$x.'" y="'.$y.'" width="'.$boxWidth.'" height="'.$boxHeight.'" fill="#ffffff" stroke="#111111" stroke-width="1.2"/>';
        $innerFrameX = $x + $frameThick;
        $innerFrameY = $y + $frameThick;
        $innerFrameW = $boxWidth - (2 * $frameThick);
        $innerFrameH = $boxHeight - (2 * $frameThick);
        $svg .= '<rect x="'.$innerFrameX.'" y="'.$innerFrameY.'" width="'.$innerFrameW.'" height="'.$innerFrameH.'" fill="#ffffff" stroke="#111111" stroke-width="1"/>';

        // Frame Miter 45-degree corner lines
        $svg .= '<line x1="'.$x.'" y1="'.$y.'" x2="'.$innerFrameX.'" y2="'.$innerFrameY.'" stroke="#111111" stroke-width="1"/>';
        $svg .= '<line x1="'.($x + $boxWidth).'" y1="'.$y.'" x2="'.($innerFrameX + $innerFrameW).'" y2="'.$innerFrameY.'" stroke="#111111" stroke-width="1"/>';
        $svg .= '<line x1="'.$x.'" y1="'.($y + $boxHeight).'" x2="'.$innerFrameX.'" y2="'.($innerFrameY + $innerFrameH).'" stroke="#111111" stroke-width="1"/>';
        $svg .= '<line x1="'.($x + $boxWidth).'" y1="'.($y + $boxHeight).'" x2="'.($innerFrameX + $innerFrameW).'" y2="'.($innerFrameY + $innerFrameH).'" stroke="#111111" stroke-width="1"/>';

        // "TN" Frame Badge at bottom-right outside corner
        $svg .= '<rect x="'.($x + $boxWidth + 2).'" y="'.($y + $boxHeight - 11).'" width="14" height="10" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
        $svg .= '<text x="'.($x + $boxWidth + 9).'" y="'.($y + $boxHeight - 3.5).'" font-family="sans-serif" font-size="7" font-weight="bold" fill="#000000" text-anchor="middle">TN</text>';

        if ($isFan) {
            // Fan Cutout Window Diagram
            $fanRadius = min($innerFrameW, $innerFrameH) * 0.28;
            $centerX = $innerFrameX + ($innerFrameW / 2);
            $centerY = $innerFrameY + ($innerFrameH / 2);

            // Realistic Glass background
            $svg .= '<rect x="'.$innerFrameX.'" y="'.$innerFrameY.'" width="'.$innerFrameW.'" height="'.$innerFrameH.'" fill="#c2ebff" stroke="#111111" stroke-width="0.8"/>';

            // FAN Tag
            $svg .= '<rect x="'.($innerFrameX + 4).'" y="'.($innerFrameY + 4).'" width="26" height="12" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
            $svg .= '<text x="'.($innerFrameX + 17).'" y="'.($innerFrameY + 13).'" font-family="sans-serif" font-size="7.5" font-weight="bold" fill="#000000" text-anchor="middle">FAN</text>';

            // Fan Outer Circle
            $svg .= '<circle cx="'.$centerX.'" cy="'.$centerY.'" r="'.$fanRadius.'" fill="#eaf2f8" stroke="#111111" stroke-width="1.2"/>';
            $svg .= '<circle cx="'.$centerX.'" cy="'.$centerY.'" r="'.($fanRadius * 0.42).'" fill="#ffffff" stroke="#111111" stroke-width="1"/>';

            for ($angle = 0; $angle < 360; $angle += 90) {
                $rad = deg2rad($angle);
                $bx1 = $centerX + ($fanRadius * 0.42) * cos($rad);
                $by1 = $centerY + ($fanRadius * 0.42) * sin($rad);
                $bx2 = $centerX + ($fanRadius * 0.95) * cos($rad + 0.28);
                $by2 = $centerY + ($fanRadius * 0.95) * sin($rad + 0.28);
                $svg .= '<line x1="'.$bx1.'" y1="'.$by1.'" x2="'.$bx2.'" y2="'.$by2.'" stroke="#111111" stroke-width="1.2"/>';
            }

            // Glass tag
            $svg .= '<circle cx="'.$centerX.'" cy="'.$centerY.'" r="7" fill="#ffffff" stroke="#111111" stroke-width="0.9"/>';
            $svg .= '<text x="'.$centerX.'" y="'.($centerY + 2.5).'" font-family="sans-serif" font-size="7" font-weight="bold" fill="#000000" text-anchor="middle">1</text>';

            $svg .= '<rect x="'.($innerFrameX + $innerFrameW - 16).'" y="'.($innerFrameY + $innerFrameH - 13).'" width="14" height="11" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
            $svg .= '<text x="'.($innerFrameX + $innerFrameW - 9).'" y="'.($innerFrameY + $innerFrameH - 4.5).'" font-family="sans-serif" font-size="7" font-weight="bold" fill="#000000" text-anchor="middle">F1</text>';

        } elseif ($isSliding) {
            // SLIDING WINDOW DIAGRAM (2-Track or 3-Track)
            $tracks = (int)($metadata['tracks'] ?? (stripos($type, '3-track') !== false ? 3 : 2));

            if ($tracks === 3) {
                $sashW = ($innerFrameW + 8) / 3;
                $panels = [
                    ['x' => $innerFrameX, 'type' => 'glass', 'tag' => 'S1', 'num' => '1'],
                    ['x' => $innerFrameX + $sashW - 4, 'type' => 'glass', 'tag' => 'S2', 'num' => '2'],
                    ['x' => $innerFrameX + ($sashW * 2) - 8, 'type' => $hasMesh ? 'mesh' : 'glass', 'tag' => 'S3', 'num' => '3']
                ];
            } else {
                $sashW = ($innerFrameW + 6) / 2;
                $panels = [
                    ['x' => $innerFrameX, 'type' => 'glass', 'tag' => 'S1', 'num' => '1'],
                    ['x' => $innerFrameX + $innerFrameW - $sashW, 'type' => $hasMesh ? 'mesh' : 'glass', 'tag' => 'S2', 'num' => '2']
                ];
            }

            foreach ($panels as $idx => $p) {
                $px = $p['x'];
                $py = $innerFrameY;
                $pw = $sashW;
                $ph = $innerFrameH;

                // Sash Outer Border
                $svg .= '<rect x="'.$px.'" y="'.$py.'" width="'.$pw.'" height="'.$ph.'" fill="#ffffff" stroke="#111111" stroke-width="1"/>';

                // Sash Top Tag (S1, S2, S3)
                $svg .= '<rect x="'.($px + ($pw / 2) - 10).'" y="'.$py.'" width="20" height="9" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
                $svg .= '<text x="'.($px + ($pw / 2)).'" y="'.($py + 6.8).'" font-family="sans-serif" font-size="6.5" font-weight="bold" fill="#000000" text-anchor="middle">'.$p['tag'].'</text>';

                $sashInnerX = $px + $sashThick;
                $sashInnerY = $py + $sashThick;
                $sashInnerW = $pw - (2 * $sashThick);
                $sashInnerH = $ph - (2 * $sashThick);

                // Sash Miter Corner lines
                $svg .= '<line x1="'.$px.'" y1="'.$py.'" x2="'.$sashInnerX.'" y2="'.$sashInnerY.'" stroke="#111111" stroke-width="0.8"/>';
                $svg .= '<line x1="'.($px + $pw).'" y1="'.$py.'" x2="'.($sashInnerX + $sashInnerW).'" y2="'.$sashInnerY.'" stroke="#111111" stroke-width="0.8"/>';
                $svg .= '<line x1="'.$px.'" y1="'.($py + $ph).'" x2="'.$sashInnerX.'" y2="'.($sashInnerY + $sashInnerH).'" stroke="#111111" stroke-width="0.8"/>';
                $svg .= '<line x1="'.($px + $pw).'" y1="'.($py + $ph).'" x2="'.($sashInnerX + $sashInnerW).'" y2="'.($sashInnerY + $sashInnerH).'" stroke="#111111" stroke-width="0.8"/>';

                if ($p['type'] === 'mesh') {
                    // MOSQUITO MESH SASH (Transom bar ST1, M3, M4, MS3, F1, Diamond wire pattern)
                    $midY = $sashInnerY + ($sashInnerH * 0.52);
                    $transomH = 6;

                    // Top Aperture with Diamond Mesh
                    $mesh1Y = $sashInnerY;
                    $mesh1H = $midY - $sashInnerY;
                    $svg .= '<rect x="'.$sashInnerX.'" y="'.$mesh1Y.'" width="'.$sashInnerW.'" height="'.$mesh1H.'" fill="#dbeef5" stroke="#111111" stroke-width="0.8"/>';
                    $meshPath1 = self::generateDiamondMeshPath($sashInnerX, $mesh1Y, $sashInnerW, $mesh1H, 4);
                    $svg .= '<path d="'.$meshPath1.'" stroke="#4f6f82" stroke-width="0.5"/>';

                    // Tag M3 in top aperture
                    $svg .= '<rect x="'.($sashInnerX + ($sashInnerW / 2) - 10).'" y="'.($mesh1Y + 8).'" width="20" height="10" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
                    $svg .= '<text x="'.($sashInnerX + ($sashInnerW / 2)).'" y="'.($mesh1Y + 15.5).'" font-family="sans-serif" font-size="7" font-weight="bold" fill="#000000" text-anchor="middle">M3</text>';

                    // Middle Transom Bar (ST1)
                    $svg .= '<rect x="'.$px.'" y="'.$midY.'" width="'.$pw.'" height="'.$transomH.'" fill="#ffffff" stroke="#111111" stroke-width="1"/>';
                    
                    // Sash Lock circle on middle rail
                    $svg .= '<circle cx="'.($px + ($pw / 2)).'" cy="'.($midY + ($transomH / 2)).'" r="4.5" fill="#ffffff" stroke="#111111" stroke-width="0.9"/>';
                    $svg .= '<line x1="'.($px + ($pw / 2) - 3).'" y1="'.($midY + ($transomH / 2)).'" x2="'.($px + ($pw / 2) + 3).'" y2="'.($midY + ($transomH / 2)).'" stroke="#111111" stroke-width="0.8"/>';

                    // ST1 / MHH / GHH Info Badge attached to middle bar
                    $tagBoxX = $px + ($pw / 2) + 3;
                    $tagBoxY = $midY + 1;
                    $ghhVal = round($hMm * 0.55);
                    $svg .= '<rect x="'.$tagBoxX.'" y="'.$tagBoxY.'" width="44" height="16" fill="#ffffff" stroke="#111111" stroke-width="0.7"/>';
                    $svg .= '<text x="'.($tagBoxX + 22).'" y="'.($tagBoxY + 5).'" font-family="sans-serif" font-size="5" font-weight="bold" fill="#000000" text-anchor="middle">ST1</text>';
                    $svg .= '<text x="'.($tagBoxX + 22).'" y="'.($tagBoxY + 10).'" font-family="sans-serif" font-size="4.5" fill="#333333" text-anchor="middle">MHH = '.$ghhVal.'</text>';
                    $svg .= '<text x="'.($tagBoxX + 22).'" y="'.($tagBoxY + 14.5).'" font-family="sans-serif" font-size="4.5" fill="#333333" text-anchor="middle">GHH = '.$ghhVal.'</text>';

                    // Bottom Aperture with Diamond Mesh
                    $mesh2Y = $midY + $transomH;
                    $mesh2H = ($sashInnerY + $sashInnerH) - $mesh2Y;
                    $svg .= '<rect x="'.$sashInnerX.'" y="'.$mesh2Y.'" width="'.$sashInnerW.'" height="'.$mesh2H.'" fill="#dbeef5" stroke="#111111" stroke-width="0.8"/>';
                    $meshPath2 = self::generateDiamondMeshPath($sashInnerX, $mesh2Y, $sashInnerW, $mesh2H, 4);
                    $svg .= '<path d="'.$meshPath2.'" stroke="#4f6f82" stroke-width="0.5"/>';

                    // Tag M4 in bottom aperture
                    $svg .= '<rect x="'.($sashInnerX + ($sashInnerW / 2) - 10).'" y="'.($mesh2Y + 8).'" width="20" height="10" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
                    $svg .= '<text x="'.($sashInnerX + ($sashInnerW / 2)).'" y="'.($mesh2Y + 15.5).'" font-family="sans-serif" font-size="7" font-weight="bold" fill="#000000" text-anchor="middle">M4</text>';

                    // MS3 Badge at bottom of mesh sash
                    $svg .= '<rect x="'.($sashInnerX + ($sashInnerW / 2) - 9).'" y="'.($sashInnerY + $sashInnerH - 8).'" width="18" height="8" fill="#ffffff" stroke="#111111" stroke-width="0.7"/>';
                    $svg .= '<text x="'.($sashInnerX + ($sashInnerW / 2)).'" y="'.($sashInnerY + $sashInnerH - 2).'" font-family="sans-serif" font-size="5.5" font-weight="bold" fill="#000000" text-anchor="middle">MS3</text>';

                    // F1 Badge at bottom right of sash
                    $svg .= '<rect x="'.($px + $pw - 14).'" y="'.($py + $ph - 11).'" width="13" height="9" fill="#ffffff" stroke="#111111" stroke-width="0.7"/>';
                    $svg .= '<text x="'.($px + $pw - 7.5).'" y="'.($py + $ph - 4.5).'" font-family="sans-serif" font-size="6" font-weight="bold" fill="#000000" text-anchor="middle">F1</text>';

                } else {
                    // REALISTIC GLASS SASH (Light Cyan/Sky-Blue Glass Tint, Sliding Direction Arrow, Handle, GHH)
                    $svg .= '<rect x="'.$sashInnerX.'" y="'.$sashInnerY.'" width="'.$sashInnerW.'" height="'.$sashInnerH.'" fill="#c2ebff" stroke="#111111" stroke-width="0.8"/>';

                    // Glass Tag Circle (e.g. 1)
                    $gCircX = $sashInnerX + ($sashInnerW * 0.48);
                    $gCircY = $sashInnerY + ($sashInnerH * 0.52);
                    $svg .= '<circle cx="'.$gCircX.'" cy="'.$gCircY.'" r="6.5" fill="#ffffff" stroke="#111111" stroke-width="0.9"/>';
                    $svg .= '<text x="'.$gCircX.'" y="'.($gCircY + 2.3).'" font-family="sans-serif" font-size="6.5" font-weight="bold" fill="#000000" text-anchor="middle">'.$p['num'].'</text>';

                    // Horizontal Sliding Direction Arrow (Points right for panel 1, left for panel 2)
                    $arrowDir = ($idx === 0) ? 1 : -1;
                    $aX1 = $gCircX + ($arrowDir * 8);
                    $aX2 = $aX1 + ($arrowDir * 16);
                    $svg .= '<line x1="'.$aX1.'" y1="'.$gCircY.'" x2="'.$aX2.'" y2="'.$gCircY.'" stroke="#1b4f72" stroke-width="1.2" marker-end="url(#slide-arrow)"/>';

                    // Handle & GHH Indicator on stile
                    $handleY = $sashInnerY + ($sashInnerH * 0.55);
                    $ghhVal = round($hMm * 0.55);
                    if ($idx === 0) {
                        // Handle projecting on left edge
                        $svg .= '<rect x="'.($px - 4).'" y="'.($handleY - 3).'" width="7" height="6" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
                        $svg .= '<line x1="'.($px - 4).'" y1="'.$handleY.'" x2="'.($px + 3).'" y2="'.$handleY.'" stroke="#111111" stroke-width="1.2"/>';
                        // GHH Tag Box
                        $svg .= '<rect x="'.$px.'" y="'.($handleY + 4).'" width="36" height="8" fill="#ffffff" stroke="#111111" stroke-width="0.7"/>';
                        $svg .= '<text x="'.($px + 18).'" y="'.($handleY + 10).'" font-family="sans-serif" font-size="5" fill="#000000" text-anchor="middle">GHH = '.$ghhVal.'</text>';
                    } else {
                        // Handle projecting on right edge
                        $svg .= '<rect x="'.($px + $pw - 3).'" y="'.($handleY - 3).'" width="7" height="6" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
                        $svg .= '<line x1="'.($px + $pw - 3).'" y1="'.$handleY.'" x2="'.($px + $pw + 4).'" y2="'.$handleY.'" stroke="#111111" stroke-width="1.2"/>';
                        // GHH Tag Box
                        $svg .= '<rect x="'.($px + $pw - 36).'" y="'.($handleY + 4).'" width="36" height="8" fill="#ffffff" stroke="#111111" stroke-width="0.7"/>';
                        $svg .= '<text x="'.($px + $pw - 18).'" y="'.($handleY + 10).'" font-family="sans-serif" font-size="5" fill="#000000" text-anchor="middle">GHH = '.$ghhVal.'</text>';
                    }
                }
            }

        } else {
            // CASEMENT / FIXED SERIES (Realistic Glass, Opening Swing, Mitered Sash)
            $isDouble = (bool)($wMm >= 1100);
            $isFixed = (bool)(stripos($type, 'fixed') !== false);

            if ($isDouble) {
                $halfInnerW = $innerFrameW / 2;
                $sashes = [
                    ['x' => $innerFrameX, 'w' => $halfInnerW, 'tag' => 'S1'],
                    ['x' => $innerFrameX + $halfInnerW, 'w' => $halfInnerW, 'tag' => 'S2']
                ];
            } else {
                $sashes = [
                    ['x' => $innerFrameX, 'w' => $innerFrameW, 'tag' => 'S1']
                ];
            }

            foreach ($sashes as $sIdx => $s) {
                $sx = $s['x'];
                $sy = $innerFrameY;
                $sw = $s['w'];
                $sh = $innerFrameH;

                if (!$isFixed) {
                    $svg .= '<rect x="'.$sx.'" y="'.$sy.'" width="'.$sw.'" height="'.$sh.'" fill="#ffffff" stroke="#111111" stroke-width="1"/>';
                    $svg .= '<rect x="'.($sx + ($sw / 2) - 10).'" y="'.$sy.'" width="20" height="9" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
                    $svg .= '<text x="'.($sx + ($sw / 2)).'" y="'.($sy + 6.8).'" font-family="sans-serif" font-size="6.5" font-weight="bold" fill="#000000" text-anchor="middle">'.$s['tag'].'</text>';

                    $sInnerX = $sx + $sashThick;
                    $sInnerY = $sy + $sashThick;
                    $sInnerW = $sw - (2 * $sashThick);
                    $sInnerH = $sh - (2 * $sashThick);

                    $svg .= '<line x1="'.$sx.'" y1="'.$sy.'" x2="'.$sInnerX.'" y2="'.$sInnerY.'" stroke="#111111" stroke-width="0.8"/>';
                    $svg .= '<line x1="'.($sx + $sw).'" y1="'.$sy.'" x2="'.($sInnerX + $sInnerW).'" y2="'.$sInnerY.'" stroke="#111111" stroke-width="0.8"/>';
                    $svg .= '<line x1="'.$sx.'" y1="'.($sy + $sh).'" x2="'.$sInnerX.'" y2="'.($sInnerY + $sInnerH).'" stroke="#111111" stroke-width="0.8"/>';
                    $svg .= '<line x1="'.($sx + $sw).'" y1="'.($sy + $sh).'" x2="'.($sInnerX + $sInnerW).'" y2="'.($sInnerY + $sInnerH).'" stroke="#111111" stroke-width="0.8"/>';

                    // Glass background
                    $svg .= '<rect x="'.$sInnerX.'" y="'.$sInnerY.'" width="'.$sInnerW.'" height="'.$sInnerH.'" fill="#c2ebff" stroke="#111111" stroke-width="0.8"/>';

                    // Casement opening swing lines (dashed)
                    if ($sIdx === 0) {
                        $svg .= '<line x1="'.$sInnerX.'" y1="'.$sInnerY.'" x2="'.($sInnerX + $sInnerW).'" y2="'.($sInnerY + ($sInnerH / 2)).'" stroke="#111111" stroke-width="0.8" stroke-dasharray="3,3"/>';
                        $svg .= '<line x1="'.$sInnerX.'" y1="'.($sInnerY + $sInnerH).'" x2="'.($sInnerX + $sInnerW).'" y2="'.($sInnerY + ($sInnerH / 2)).'" stroke="#111111" stroke-width="0.8" stroke-dasharray="3,3"/>';
                    } else {
                        $svg .= '<line x1="'.($sInnerX + $sInnerW).'" y1="'.$sInnerY.'" x2="'.$sInnerX.'" y2="'.($sInnerY + ($sInnerH / 2)).'" stroke="#111111" stroke-width="0.8" stroke-dasharray="3,3"/>';
                        $svg .= '<line x1="'.($sInnerX + $sInnerW).'" y1="'.($sInnerY + $sInnerH).'" x2="'.$sInnerX.'" y2="'.($sInnerY + ($sInnerH / 2)).'" stroke="#111111" stroke-width="0.8" stroke-dasharray="3,3"/>';
                    }

                    // Handle & OUT badge
                    $handleY = $sInnerY + ($sInnerH * 0.52);
                    $ghhVal = round($hMm * 0.52);
                    $svg .= '<rect x="'.($sx + ($sw / 2) - 10).'" y="'.($handleY + 3).'" width="20" height="9" fill="#ffffff" stroke="#111111" stroke-width="0.7"/>';
                    $svg .= '<text x="'.($sx + ($sw / 2)).'" y="'.($handleY + 9.5).'" font-family="sans-serif" font-size="5.5" font-weight="bold" fill="#000000" text-anchor="middle">OUT</text>';
                } else {
                    // Pure fixed glass
                    $svg .= '<rect x="'.$sx.'" y="'.$sy.'" width="'.$sw.'" height="'.$sh.'" fill="#c2ebff" stroke="#111111" stroke-width="0.8"/>';
                    $svg .= '<rect x="'.($sx + ($sw / 2) - 15).'" y="'.($sy + ($sh / 2) - 6).'" width="30" height="12" fill="#ffffff" stroke="#111111" stroke-width="0.7"/>';
                    $svg .= '<text x="'.($sx + ($sw / 2)).'" y="'.($sy + ($sh / 2) + 2.5).'" font-family="sans-serif" font-size="6.5" font-weight="bold" fill="#000000" text-anchor="middle">FIXED</text>';
                }
            }

            // F1 Badge bottom right
            $svg .= '<rect x="'.($innerFrameX + $innerFrameW - 15).'" y="'.($innerFrameY + $innerFrameH - 12).'" width="14" height="10" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
            $svg .= '<text x="'.($innerFrameX + $innerFrameW - 8).'" y="'.($innerFrameY + $innerFrameH - 4.5).'" font-family="sans-serif" font-size="6.5" font-weight="bold" fill="#000000" text-anchor="middle">F1</text>';
        }

        // --- 2. DIMENSION LINES ---
        $dimBaseY = $y + $boxHeight;

        if ($isSliding) {
            // Intermediate Panel Dimensions (e.g. 738 | 738)
            $midX = $x + ($boxWidth / 2);
            $panelValMm = round($wMm / 2);

            // Left panel dimension arrow
            $svg .= '<line x1="'.$x.'" y1="'.($dimBaseY + 9).'" x2="'.$midX.'" y2="'.($dimBaseY + 9).'" stroke="#444444" stroke-width="0.8" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>';
            $svg .= '<text x="'.($x + ($boxWidth / 4)).'" y="'.($dimBaseY + 8).'" font-family="sans-serif" font-size="8" font-weight="bold" fill="#111111" text-anchor="middle">'.$panelValMm.'</text>';

            // Right panel dimension arrow
            $svg .= '<line x1="'.$midX.'" y1="'.($dimBaseY + 9).'" x2="'.($x + $boxWidth).'" y2="'.($dimBaseY + 9).'" stroke="#444444" stroke-width="0.8" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>';
            $svg .= '<text x="'.($midX + ($boxWidth / 4)).'" y="'.($dimBaseY + 8).'" font-family="sans-serif" font-size="8" font-weight="bold" fill="#111111" text-anchor="middle">'.$panelValMm.'</text>';

            // Total Width Dimension Arrow (Preserves exact test string)
            $totalArrowY = $dimBaseY + 22;
            $svg .= '<line x1="'.$x.'" y1="'.$totalArrowY.'" x2="'.($x + $boxWidth).'" y2="'.$totalArrowY.'" stroke="#222222" stroke-width="0.9" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>';
            $svg .= '<text x="'.($x + ($boxWidth / 2)).'" y="'.($totalArrowY - 1).'" font-family="sans-serif" font-size="10" font-weight="bold" fill="#000000" text-anchor="middle">'.number_format($w, 2, '.', '').$unitLabel.'</text>';

            // --- 3. TRACK VIEW CROSS-SECTION ---
            $trackY = $totalArrowY + 10;
            $trackH = 9;
            
            // "OUT" Label
            $svg .= '<text x="'.($x + ($boxWidth * 0.35)).'" y="'.($trackY - 2).'" font-family="sans-serif" font-size="5.5" font-weight="bold" fill="#333333" text-anchor="middle">OUT</text>';
            
            // Track Outline
            $svg .= '<rect x="'.$x.'" y="'.$trackY.'" width="'.$boxWidth.'" height="'.$trackH.'" fill="#ffffff" stroke="#222222" stroke-width="0.9"/>';
            $svg .= '<line x1="'.$x.'" y1="'.($trackY + ($trackH / 2)).'" x2="'.($x + $boxWidth).'" y2="'.($trackY + ($trackH / 2)).'" stroke="#888888" stroke-width="0.7"/>';

            // Outer Track Sash (Left)
            $tSashW = ($boxWidth * 0.52);
            $svg .= '<rect x="'.$x.'" y="'.($trackY + 1).'" width="'.$tSashW.'" height="'.($trackH * 0.45).'" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';

            // Inner Track Sash (Right) with Solid Black Bar for Mesh Screen
            $tSash2X = $x + $boxWidth - $tSashW;
            $svg .= '<rect x="'.$tSash2X.'" y="'.($trackY + ($trackH * 0.5)).'" width="'.$tSashW.'" height="'.($trackH * 0.45).'" fill="#ffffff" stroke="#111111" stroke-width="0.8"/>';
            if ($hasMesh) {
                $svg .= '<rect x="'.$tSash2X.'" y="'.($trackY + $trackH - 1.5).'" width="'.$tSashW.'" height="2" fill="#000000"/>';
            }

            // "IN" Label
            $svg .= '<text x="'.($x + ($boxWidth * 0.35)).'" y="'.($trackY + $trackH + 7).'" font-family="sans-serif" font-size="5.5" font-weight="bold" fill="#333333" text-anchor="middle">IN</text>';

        } else {
            // Width Dimension for Casement / Fixed
            $arrowY = $dimBaseY + 12;
            $svg .= '<line x1="'.$x.'" y1="'.$arrowY.'" x2="'.($x + $boxWidth).'" y2="'.$arrowY.'" stroke="#333333" stroke-width="1" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>';
            $svg .= '<text x="'.($x + ($boxWidth / 2)).'" y="'.($arrowY + 12).'" font-family="sans-serif" font-size="9" font-weight="bold" fill="#000000" text-anchor="middle">'.number_format($w, 2, '.', '').$unitLabel.'</text>';
        }

        // Height Dimension Arrow (Left) (Preserves exact test string)
        $arrowX = $x - 11;
        $svg .= '<line x1="'.$arrowX.'" y1="'.$y.'" x2="'.$arrowX.'" y2="'.($y + $boxHeight).'" stroke="#333333" stroke-width="1" marker-start="url(#arr-start)" marker-end="url(#arr-end)"/>';
        $svg .= '<text x="'.($arrowX - 4).'" y="'.($y + ($boxHeight / 2)).'" font-family="sans-serif" font-size="9" font-weight="bold" fill="#000000" text-anchor="middle" transform="rotate(-90, '.($arrowX - 4).', '.($y + ($boxHeight / 2)).')">'.number_format($h, 2, '.', '').$unitLabel.'</text>';

        $svg .= '</svg>';

        return $svg;
    }

    /**
     * Generate mathematically bounded diagonal diamond wire mesh paths for DomPDF rendering.
     */
    private static function generateDiamondMeshPath($x0, $y0, $w, $h, $step = 4)
    {
        $dPath = '';
        $minD = (int)floor($x0 - ($y0 + $h));
        $maxD = (int)ceil(($x0 + $w) - $y0);
        for ($d = $minD; $d <= $maxD; $d += $step) {
            $xMin = max($x0, $y0 + $d);
            $xMax = min($x0 + $w, $y0 + $h + $d);
            if ($xMin < $xMax) {
                $y1 = $xMin - $d;
                $y2 = $xMax - $d;
                $dPath .= sprintf('M%.1f,%.1f L%.1f,%.1f ', $xMin, $y1, $xMax, $y2);
            }
        }
        $minS = (int)floor($x0 + $y0);
        $maxS = (int)ceil($x0 + $w + $y0 + $h);
        for ($s = $minS; $s <= $maxS; $s += $step) {
            $xMin = max($x0, $s - ($y0 + $h));
            $xMax = min($x0 + $w, $s - $y0);
            if ($xMin < $xMax) {
                $y1 = $s - $xMin;
                $y2 = $s - $xMax;
                $dPath .= sprintf('M%.1f,%.1f L%.1f,%.1f ', $xMin, $y1, $xMax, $y2);
            }
        }
        return $dPath;
    }
}
