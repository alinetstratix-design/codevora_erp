<?php

namespace App\DTOs;

class TermsDTO
{
    public array $terms;
    public array $installationPrerequisites;
    public array $siteReadinessChecklist;
    public string $qualityAssurance;
    public string $variationOrderClause;
    public string $acceptanceText;

    public function __construct(array $terms = [], array $installationPrerequisites = [], string $acceptanceText = '')
    {
        $this->terms = !empty($terms) ? $terms : [
            "Payments terms: -\n  a. 100% Advance along with order, if it is less than INR 100000.\n  b. 50% advance along with order, 50% before delivery, if it is more than INR 100000.",
            "Validation of quote 30 days, total execution of project should be completed latest by 3-months.",
            "P.O & Payments should made in the name of SHANI CORPORATION LIMITED.",
            "The prices are based on the sizes provided by the customer. The prices are valid for variation in sizes up to +/- 30mm per window provided the design and style of product remains unchanged. The customer will be charged on pro-rate basis for difference between the actual sizes and given sizes, if any, beyond the above variation.",
            "After handovering the windows, cleaning not our scope.",
            "Windows security tape should be remove while installing windows freely, After installation security tape will be removed by us that should be chargeable per window INR 100.",
            "If any other commitments given by our sales team, before placing order please call us . Cell : +91 9599543500",
            "After handovering windows, If any service require related to windows & doors , that should be chargeable. Per visit - INR 350.",
            "Material unloading & storage should be your scope.",
            "All disputes shall be subject jurisdiction only."
        ];

        $this->installationPrerequisites = !empty($installationPrerequisites) ? $installationPrerequisites : [
            "Walls should be plastered from inside and outside, with inside POP complete.",
            "All jams, sills and soffits should be plastered.",
            "Flooring (where doors have to be installed) should be complete.",
            "Aperture should be smooth.",
            "Base and top of window should be water leveled and sides should be in vertical plump.",
            "Sill width should be more than the window width.",
            "Opening should be accessible from inside for installation.",
            "Grills: Adequate care should be taken if grills have to be installed.\n  a. For Horizontal slider Window: Grill should be provided on the outer face of slider before the installation of the window.\n  b. For Casement windows: Screw type grill is recommended after installation of casement window.",
            "Installation should happen before the last coat of paint. At least one coat of paint should be done before installation begins.",
            "Scaffoldings/ bracing should not interrupt the window openings where openings where windows are supposed to be installed."
        ];

        $this->siteReadinessChecklist = [
            '✓ Opening dimensions ready & plastered inside/outside',
            '✓ Flooring completed at door locations',
            '✓ 230V Single-phase electricity available at site',
            '✓ Safe, dry, and locked storage area available on-site',
            '✓ Unrestricted aperture access for installation crew',
            '✓ Civil jamb plastering completed prior to installation',
        ];

        $this->qualityAssurance = "Quality Assurance Commitment: Material supplied will be exactly as specified in this quotation. No material substitution will be made without explicit written approval. Any design or size modification requested by the customer after order approval will be chargeable.";

        $this->variationOrderClause = "Variation Order Clause: Any additional supply or site work requested beyond the scope specified in this quotation shall be treated as a Variation Order and will be quoted separately.";

        $this->acceptanceText = !empty($acceptanceText) ? $acceptanceText : "I hereby accept the estimate as per above mentioned price and specifications. I have read and understood the terms & conditions and agree to them.";
    }

    public function toArray(): array
    {
        return [
            'terms' => $this->terms,
            'installation_prerequisites' => $this->installationPrerequisites,
            'site_readiness_checklist' => $this->siteReadinessChecklist,
            'quality_assurance' => $this->qualityAssurance,
            'variation_order_clause' => $this->variationOrderClause,
            'acceptance_text' => $this->acceptanceText,
        ];
    }
}
