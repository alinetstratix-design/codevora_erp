<?php

namespace App\DTOs;

use Carbon\Carbon;

class QuotationHeaderDTO
{
    public string $quoteNo;
    public string $date;
    public string $formattedDate;
    public string $validTill;
    public string $formattedValidTill;
    public string $status;
    public string $opportunityNo;
    public string $formattedMetaBar;

    public function __construct(array $data = [])
    {
        $this->quoteNo = $data['quote_no'] ?? $data['quotation_number'] ?? 'SCL-QT-00001831';
        $this->date = $data['date'] ?? $data['quotation_date'] ?? date('Y-m-d');
        $this->formattedDate = Carbon::parse($this->date)->format('d-m-Y');
        
        $valid = $data['valid_till'] ?? $data['valid_until'] ?? date('Y-m-d', strtotime('+30 days'));
        $this->validTill = $valid;
        $this->formattedValidTill = Carbon::parse($valid)->format('d-m-Y');
        
        $this->status = $data['status'] ?? 'Draft';
        $this->opportunityNo = $data['opportunity_no'] ?? '';
        
        $projectName = $data['project_name'] ?? 'AMBALA AIRFORCE';
        $this->formattedMetaBar = "Quote No. : {$this->quoteNo} / Project : {$projectName} / Date : {$this->formattedDate}";
    }

    public function toArray(): array
    {
        return [
            'quote_no' => $this->quoteNo,
            'date' => $this->date,
            'formatted_date' => $this->formattedDate,
            'valid_till' => $this->validTill,
            'formatted_valid_till' => $this->formattedValidTill,
            'status' => $this->status,
            'opportunity_no' => $this->opportunityNo,
            'formatted_meta_bar' => $this->formattedMetaBar,
        ];
    }
}
