<?php

namespace App\DTOs;

class QuotationReportDTO
{
    public CompanyDTO $company;
    public CustomerDTO $customer;
    public ProjectDTO $project;
    public QuotationHeaderDTO $header;
    public QuotationSummaryDTO $summary;
    public FinancialSummaryDTO $financials;
    /** @var ItemDTO[] */
    public array $items;
    public TermsDTO $terms;
    public BankDTO $bank;
    public SignatureDTO $signature;
    public FooterDTO $footer;
    public array $enclosures;

    public function __construct(
        CompanyDTO $company,
        CustomerDTO $customer,
        ProjectDTO $project,
        QuotationHeaderDTO $header,
        QuotationSummaryDTO $summary,
        FinancialSummaryDTO $financials,
        array $items,
        TermsDTO $terms,
        BankDTO $bank,
        SignatureDTO $signature,
        FooterDTO $footer,
        array $enclosures = []
    ) {
        $this->company = $company;
        $this->customer = $customer;
        $this->project = $project;
        $this->header = $header;
        $this->summary = $summary;
        $this->financials = $financials;
        $this->items = $items;
        $this->terms = $terms;
        $this->bank = $bank;
        $this->signature = $signature;
        $this->footer = $footer;
        $this->enclosures = !empty($enclosures) ? $enclosures : [
            'a. Window design, specification and value',
            'b. Terms and Conditions'
        ];
    }

    public function toArray(): array
    {
        return [
            'company' => $this->company->toArray(),
            'customer' => $this->customer->toArray(),
            'project' => $this->project->toArray(),
            'header' => $this->header->toArray(),
            'summary' => $this->summary->toArray(),
            'financials' => $this->financials->toArray(),
            'items' => array_map(fn(ItemDTO $item) => $item->toArray(), $this->items),
            'terms' => $this->terms->toArray(),
            'bank' => $this->bank->toArray(),
            'signature' => $this->signature->toArray(),
            'footer' => $this->footer->toArray(),
            'enclosures' => $this->enclosures,
        ];
    }
}
