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
        $this->companyName = $data['company_name'] ?? 'SHANI CORPORATION LIMITED';
        $this->logo = $data['logo'] ?? '';
        $this->address = $data['address'] ?? 'D-42, E-42 & E-43 , Gopalpur Industrial Area , Sikandrabad , Bulandshar , Uttar Pradesh -203205';
        $this->phone = $data['phone'] ?? '+91 9599543500';
        $this->email = $data['email'] ?? 'query@sclgroup.co';
        $this->website = $data['website'] ?? '';
        $this->gstin = $data['gstin'] ?? '';
        $this->formattedContactLine = "Contact No. : {$this->phone} | Email : {$this->email}";
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
