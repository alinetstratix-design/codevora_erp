<?php

namespace App\DTOs;

class CompanyDTO
{
    public string $companyName;
    public string $logo;
    public string $address;
    public string $phone;
    public string $email;
    public string $website;
    public string $gstin;
    public string $formattedContactLine;

    public function __construct(array $data = [])
    {
        $this->companyName = $data['company_name'] ?? config('app.name', 'Codevora ERP');
        $this->logo = $data['logo'] ?? '';
        $this->address = $data['address'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->website = $data['website'] ?? '';
        $this->gstin = $data['gstin'] ?? '';
        $contactParts = [];
        if (!empty($this->phone)) $contactParts[] = "Contact No. : {$this->phone}";
        if (!empty($this->email)) $contactParts[] = "Email : {$this->email}";
        $this->formattedContactLine = implode(' | ', $contactParts);
    }

    public function toArray(): array
    {
        return [
            'company_name' => $this->companyName,
            'logo' => $this->logo,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'gstin' => $this->gstin,
            'formatted_contact_line' => $this->formattedContactLine,
        ];
    }
}
