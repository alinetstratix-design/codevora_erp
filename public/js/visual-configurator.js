/**
 * VisualConfigurator - Hydrates SVG templates with live dimensions.
 * Handles parsing variables like {{WIDTH}}, {{HEIGHT}}, and math formulas.
 */
class VisualConfigurator {
    constructor(containerSelector) {
        this.container = document.querySelector(containerSelector);
        this.template = '';
        this.dimensions = { width: 1000, height: 1000, unit: 'mm' };
    }

    setTemplate(svgString) {
        this.template = svgString;
        this.render();
    }

    setDimensions(width, height, unit = 'mm') {
        this.dimensions.width = parseFloat(width) || 1000;
        this.dimensions.height = parseFloat(height) || 1000;
        this.dimensions.unit = unit || 'mm';
        this.render();
    }

    calculateVariables() {
        const rawW = this.dimensions.width;
        const rawH = this.dimensions.height;
        const unit = (this.dimensions.unit || 'mm').trim();

        // Conversion factors to mm for consistent geometry scaling
        const toMm = {
            'mm': 1,
            'cm': 10,
            'inch': 25.4,
            'in': 25.4,
            'ft': 304.8,
            'feet': 304.8,
            'm': 1000
        };
        const factor = toMm[unit.toLowerCase()] || 1;

        // Normalized geometry in mm scale
        const w = Math.max(100, rawW * factor);
        const h = Math.max(100, rawH * factor);

        const frameThickness = 40;
        const panelOverlap = 20;

        // Basic inner dimensions inside the outer frame
        const innerW = Math.max(20, w - (frameThickness * 2));
        const innerH = Math.max(20, h - (frameThickness * 2));

        // Center points for labels
        const centerX = w / 2;
        const centerY = h / 2;

        // For Sliding 2 Track (2 panels)
        const panelW = (innerW / 2) + (panelOverlap / 2);
        const panel2X = frameThickness + panelW - panelOverlap;
        
        // Slide Arrow
        const slideArrowEnd = centerX - 150 > 0 ? centerX - 150 : 0;

        // ViewBox padding to ensure dimension lines & labels never get clipped
        const pad = 60;
        const vbX = -pad;
        const vbY = -pad;
        const vbW = w + (pad * 2);
        const vbH = h + (pad * 2);

        return {
            '{{WIDTH}} mm': `${rawW} ${unit}`,
            '{{HEIGHT}} mm': `${rawH} ${unit}`,
            '{{RAW_WIDTH}}': rawW,
            '{{RAW_HEIGHT}}': rawH,
            '{{UNIT}}': unit,
            '{{WIDTH}}': w,
            '{{HEIGHT}}': h,
            '{{INNER_WIDTH}}': innerW,
            '{{INNER_HEIGHT}}': innerH,
            '{{CENTER_X}}': centerX,
            '{{CENTER_Y}}': centerY,
            '{{PANEL_WIDTH}}': panelW,
            '{{PANEL_2_X}}': panel2X,
            '{{SLIDE_ARROW_END}}': slideArrowEnd,
            '{{VB_X}}': vbX,
            '{{VB_Y}}': vbY,
            '{{VB_WIDTH}}': vbW,
            '{{VB_HEIGHT}}': vbH
        };
    }

    render() {
        if (!this.template || !this.container) return;

        let hydratedSvg = this.template;
        const vars = this.calculateVariables();

        for (const [key, value] of Object.entries(vars)) {
            // Replace all occurrences of the variable
            const regex = new RegExp(key.replace(/([.*+?^=!:${}()|\[\]\/\\])/g, "\\$1"), 'g');
            hydratedSvg = hydratedSvg.replace(regex, value);
        }

        this.container.innerHTML = hydratedSvg;
        const svgEl = this.container.querySelector('svg');
        if (svgEl) {
            svgEl.style.maxWidth = '100%';
            svgEl.style.maxHeight = '380px';
            svgEl.style.width = 'auto';
            svgEl.style.height = 'auto';
            svgEl.style.objectFit = 'contain';
            svgEl.style.display = 'block';
            svgEl.style.margin = '0 auto';
        }
    }
}

// Make it globally available
window.VisualConfigurator = VisualConfigurator;
