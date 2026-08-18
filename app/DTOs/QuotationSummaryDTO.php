<?php

namespace App\DTOs;

class QuotationSummaryDTO
{
    public int $noOfComponents;
    public string $formattedNoOfComponents;

    public float $totalAreaSqft;
    public string $formattedTotalAreaSqft;

    public float $basicValue;
    public string $formattedBasicValue;

    public float $totalProjectCost;
    public string $formattedTotalProjectCost;

    public float $avgPriceSqftExGst;
    public string $formattedAvgPriceSqftExGst;

    public float $avgPriceSqftIncGst;
    public string $formattedAvgPriceSqftIncGst;

    public function __construct(array $data = [])
    {
        $this->noOfComponents = (int)($data['no_of_components'] ?? 0);
        $this->formattedNoOfComponents = $this->noOfComponents . ' Pcs';

        $this->totalAreaSqft = (float)($data['total_area_sqft'] ?? 0);
        $this->formattedTotalAreaSqft = number_format($this->totalAreaSqft, 2, '.', '') . ' Sq.Ft.';

        $this->basicValue = (float)($data['basic_value'] ?? $data['subtotal'] ?? 0);
        $this->formattedBasicValue = number_format($this->basicValue, 2, '.', ',') . ' INR';

        $this->totalProjectCost = (float)($data['total_project_cost'] ?? $this->basicValue);
        $this->formattedTotalProjectCost = number_format($this->totalProjectCost, 2, '.', ',') . ' INR';

        $this->avgPriceSqftExGst = (float)($data['avg_price_sqft_ex_gst'] ?? 0);
        $this->formattedAvgPriceSqftExGst = number_format($this->avgPriceSqftExGst, 2, '.', '') . ' INR';

        $this->avgPriceSqftIncGst = (float)($data['avg_price_sqft_inc_gst'] ?? 0);
        $this->formattedAvgPriceSqftIncGst = number_format($this->avgPriceSqftIncGst, 2, '.', '') . ' INR';
    }

    public function toArray(): array
    {
        return [
            'no_of_components' => $this->noOfComponents,
            'formatted_no_of_components' => $this->formattedNoOfComponents,
            'total_area_sqft' => $this->totalAreaSqft,
            'formatted_total_area_sqft' => $this->formattedTotalAreaSqft,
            'basic_value' => $this->basicValue,
            'formatted_basic_value' => $this->formattedBasicValue,
            'total_project_cost' => $this->totalProjectCost,
            'formatted_total_project_cost' => $this->formattedTotalProjectCost,
            'avg_price_sqft_ex_gst' => $this->avgPriceSqftExGst,
            'formatted_avg_price_sqft_ex_gst' => $this->formattedAvgPriceSqftExGst,
            'avg_price_sqft_inc_gst' => $this->avgPriceSqftIncGst,
            'formatted_avg_price_sqft_inc_gst' => $this->formattedAvgPriceSqftIncGst,
        ];
    }
}
