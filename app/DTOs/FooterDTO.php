<?php

namespace App\DTOs;

class FooterDTO
{
    public string $pageTextFormat;
    public string $poweredBy;

    public function __construct(string $pageTextFormat = '{PAGE_NUM} of {PAGE_COUNT}', string $poweredBy = 'powered by Codevora Tech')
    {
        $this->pageTextFormat = $pageTextFormat;
        $this->poweredBy = $poweredBy;
    }

    public function toArray(): array
    {
        return [
            'page_text_format' => $this->pageTextFormat,
            'powered_by' => $this->poweredBy,
        ];
    }
}
