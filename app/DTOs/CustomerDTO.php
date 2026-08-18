<?php

namespace App\DTOs;

class CustomerDTO
{
    public string $name;
    public string $companyName;
    public string $phone;
    public string $email;
    public string $address;
    public string $gstNumber;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? 'AMBALA AIRFORCE';
        $this->companyName = $data['company_name'] ?? '';
        $this->phone = $data['phone'] ?? '';
        $this->email = $data['email'] ?? '';
        $this->address = $data['address'] ?? '';
        $this->gstNumber = $data['gst_number'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'company_name' => $this->companyName,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'gst_number' => $this->gstNumber,
        ];
    }
}
