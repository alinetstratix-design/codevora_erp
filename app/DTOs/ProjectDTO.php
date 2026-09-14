<?php

namespace App\DTOs;

class ProjectDTO
{
    public string $name;
    public string $location;
    public string $salesPerson;
    public string $remarks;

    public function __construct(array $data = [])
    {
        $this->name = $data['name'] ?? $data['project_name'] ?? 'Project';
        $this->location = $data['location'] ?? $data['project_location'] ?? $data['address'] ?? '';
        $this->salesPerson = $data['sales_person'] ?? 'Authorized Signatory';
        $this->remarks = $data['remarks'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'location' => $this->location,
            'sales_person' => $this->salesPerson,
            'remarks' => $this->remarks,
        ];
    }
}
