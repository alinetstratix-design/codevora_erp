<?php

namespace App\DTOs;

class FinancialSummaryDTO
{
    public float $subtotal;
    public string $formattedSubtotal;

    public float $discount;
    public string $formattedDiscount;
    public float $discountPercent;

    public float $transportation;
    public string $formattedTransportation;

    public float $installation;
    public string $formattedInstallation;

    public float $taxableAmount;
    public string $formattedTaxableAmount;

    public float $gstPercent;
    public float $gstAmount;
    public string $formattedGstAmount;

    public float $additionalCharges;
    public string $formattedAdditionalCharges;

    public float $grandTotal;
    public string $formattedGrandTotal;

    public string $amountInWords;

    public function __construct(array $data = [])
    {
        $this->subtotal = (float)($data['subtotal'] ?? $data['basic_value'] ?? 0);
        $this->formattedSubtotal = number_format($this->subtotal, 2, '.', ',') . ' INR';

        $this->discount = (float)($data['discount'] ?? 0);
        $this->formattedDiscount = number_format($this->discount, 2, '.', ',') . ' INR';
        $this->discountPercent = (float)($data['discount_percent'] ?? 0);

        $this->transportation = (float)($data['transportation'] ?? $data['freight_charges'] ?? 0);
        $this->formattedTransportation = number_format($this->transportation, 2, '.', ',') . ' INR';

        $this->installation = (float)($data['installation'] ?? $data['installation_cost'] ?? 0);
        $this->formattedInstallation = number_format($this->installation, 2, '.', ',') . ' INR';

        $this->taxableAmount = $this->subtotal - $this->discount + $this->transportation + $this->installation;
        $this->formattedTaxableAmount = number_format($this->taxableAmount, 2, '.', ',') . ' INR';

        $this->gstPercent = (float)($data['gst_percent'] ?? $data['tax_percent'] ?? 18);
        $this->gstAmount = (float)($data['gst'] ?? $data['tax_amount'] ?? 0);
        $this->formattedGstAmount = number_format($this->gstAmount, 2, '.', ',') . ' INR';

        $this->additionalCharges = (float)($data['additional_charges'] ?? 0);
        $this->formattedAdditionalCharges = number_format($this->additionalCharges, 2, '.', ',') . ' INR';

        $this->grandTotal = (float)($data['grand_total'] ?? 0);
        $this->formattedGrandTotal = number_format($this->grandTotal, 2, '.', ',') . ' INR';

        $this->amountInWords = $data['amount_in_words'] ?? '';
    }

    public function toArray(): array
    {
        return [
            'subtotal' => $this->subtotal,
            'formatted_subtotal' => $this->formattedSubtotal,
            'discount' => $this->discount,
            'formatted_discount' => $this->formattedDiscount,
            'discount_percent' => $this->discountPercent,
            'transportation' => $this->transportation,
            'formatted_transportation' => $this->formattedTransportation,
            'installation' => $this->installation,
            'formatted_installation' => $this->formattedInstallation,
            'taxable_amount' => $this->taxableAmount,
            'formatted_taxable_amount' => $this->formattedTaxableAmount,
            'gst_percent' => $this->gstPercent,
            'gst_amount' => $this->gstAmount,
            'formatted_gst_amount' => $this->formattedGstAmount,
            'additional_charges' => $this->additionalCharges,
            'formatted_additional_charges' => $this->formattedAdditionalCharges,
            'grand_total' => $this->grandTotal,
            'formatted_grand_total' => $this->formattedGrandTotal,
            'amount_in_words' => $this->amountInWords,
        ];
    }
}
