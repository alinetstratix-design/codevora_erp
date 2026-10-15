<?php

/*
|--------------------------------------------------------------------------
| Quotation template defaults
|--------------------------------------------------------------------------
| Used once, when a new company is created, to fill its Company Settings.
| After that, each company owns and edits its own terms. Nothing in the
| generated PDF falls back to these values, so an empty company setting
| is caught by the approval checks instead of printing someone else's terms.
*/

return [

    'footer_powered_by' => 'Powered by Codevora AI',

    // Starting point for a company's commercial terms (editable in Company Settings).
    'terms' => [
        "Payment terms: 100% advance along with the order if the value is below INR 1,00,000; 50% advance along with the order and 50% before delivery if the value is above INR 1,00,000.",
        "Validity of this quotation: 30 days. Project execution should be completed within 3 months of order.",
        "Purchase order and payments should be made in favour of the authorised company entity.",
        "Prices are based on the sizes provided by the customer and are valid for size variation of up to +/- 30 mm per window, provided the design and style remain unchanged. Any difference beyond this is charged pro-rata.",
        "Cleaning after handover is not in our scope.",
        "The security tape on windows is removed by us after installation, chargeable at INR 100 per window.",
        "Commitments made outside this quotation must be confirmed with company management before the order is placed.",
        "Service visits after handover (windows and doors) are chargeable at INR 350 per visit.",
        "Material unloading and storage at site are the customer's responsibility.",
        "All disputes are subject to local jurisdiction only.",
    ],

    'installation_prerequisites' => [
        "Walls should be plastered inside and outside, with inside POP complete.",
        "All jambs, sills and soffits should be plastered.",
        "Flooring at door locations should be complete.",
        "Apertures should be smooth.",
        "The base and top of the window should be level and the sides vertical.",
        "The sill width should be more than the window width.",
        "The opening should be accessible from inside for installation.",
        "Grills: take care if grills are required. For horizontal slider windows, the grill is fitted on the outer face of the slider before installation. For casement windows, screw-type grills are recommended after installation.",
        "Installation should happen before the last coat of paint; at least one coat of paint should be done first.",
        "Scaffolding or bracing should not block the window openings.",
    ],

    // Standard clauses printed on every quotation. Same wording for all companies.
    'clauses' => [
        'site_readiness' => [
            '✓ Opening dimensions ready & plastered inside/outside',
            '✓ Flooring completed at door locations',
            '✓ 230V single-phase electricity available at site',
            '✓ Safe, dry, and locked storage area available on site',
            '✓ Unrestricted aperture access for installation crew',
            '✓ Civil jamb plastering completed prior to installation',
        ],
        'quality_assurance' => 'Quality Assurance Commitment: Material supplied will be exactly as specified in this quotation. No material substitution will be made without explicit written approval. Any design or size modification requested by the customer after order approval will be chargeable.',
        'variation_order' => 'Variation Order Clause: Any additional supply or site work requested beyond the scope specified in this quotation shall be treated as a Variation Order and will be quoted separately.',
        'acceptance' => 'I hereby accept the estimate as per above mentioned price and specifications. I have read and understood the terms & conditions and agree to them.',
    ],

    /*
    | Product specification values printed on every quotation item. Companies enter their own
    | values in Company Settings. Nothing is pre-filled, so no one else's claims get printed.
    */
    'item_spec_fields' => [
        'wall_thickness' => ['Wall Thickness', 'e.g. 2.5 mm Class A'],
        'insulation_chambers' => ['Insulation Chambers', 'e.g. 3-chamber'],
        'steel_reinforcement' => ['Steel Reinforcement', 'e.g. Galvanized steel box'],
        'glass_make' => ['Glass Make', 'e.g. Brand name of glass supplier'],
        'glass_performance' => ['Glass Performance', 'e.g. Acoustic / solar control'],
        'locking_system' => ['Locking System', 'e.g. Multi-point lock'],
        'hinges_stays' => ['Hinges / Stays', 'e.g. SS304 friction stay'],
        'drainage_system' => ['Drainage System', 'e.g. Concealed drainage caps'],
        'warranty' => ['Warranty', 'e.g. 10 years profile / 1 year hardware'],
        'delivery_timeline' => ['Delivery Timeline', 'e.g. 2 to 3 weeks from order confirmation'],
    ],
];
