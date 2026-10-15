<?php

namespace App\DTOs;

/**
 * Commercial terms for one quotation.
 *
 * Company-owned lists (terms, prerequisites) are never filled with defaults:
 * an empty list makes the "warranty / terms defined" check fail instead of
 * silently printing another company's terms. Standard clauses come from config.
 */
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
        $clauses = config('quotation_defaults.clauses');

        $this->terms = $terms;
        $this->installationPrerequisites = $installationPrerequisites;
        $this->siteReadinessChecklist = $clauses['site_readiness'];
        $this->qualityAssurance = $clauses['quality_assurance'];
        $this->variationOrderClause = $clauses['variation_order'];
        $this->acceptanceText = $acceptanceText !== '' ? $acceptanceText : $clauses['acceptance'];
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
