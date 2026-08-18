<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quotation_number' => $this->quotation_number ?? $this->quote_no,
            'project_name' => $this->project_name,
            'client_name' => $this->client_name,
            'date' => $this->date ?? $this->quotation_date,
            'quote_no' => $this->quote_no ?? $this->quotation_number,
            'status' => $this->status,
            'valid_till' => $this->valid_till ?? $this->valid_until,
            'opportunity_no' => $this->opportunity_no,
            'address' => $this->address ?? $this->project_location,
            
            'financials' => [
                'basic_value' => (float) ($this->basic_value ?? $this->subtotal),
                'material_value' => (float) $this->material_value,
                'net_value' => (float) $this->net_value,
                'total_glass_cost' => (float) $this->total_glass_cost,
                'installation_cost_rate' => (float) $this->installation_cost_rate,
                'freight_charges_rate' => (float) $this->freight_charges_rate,
                'installation_cost' => (float) ($this->installation_cost ?? $this->installation),
                'freight_charges' => (float) ($this->freight_charges ?? $this->transportation),
                'subtotal' => (float) $this->subtotal,
                'discount' => (float) ($this->discount ?? 0),
                'discount_percent' => (float) $this->discount_percent,
                'tax_percent' => (float) ($this->tax_percent ?? 18),
                'tax_amount' => (float) ($this->tax_amount ?? $this->gst),
                'additional_charges' => (float) $this->additional_charges,
                'total_project_cost' => (float) ($this->total_project_cost ?? $this->subtotal),
                'grand_total' => (float) $this->grand_total,
                'avg_price_sqft_ex_gst' => (float) $this->avg_price_sqft_ex_gst,
                'avg_price_sqft_inc_gst' => (float) $this->avg_price_sqft_inc_gst,
                'amount_in_words' => $this->amount_in_words,
            ],
            
            'metrics' => [
                'no_of_components' => (int) ($this->no_of_components ?? ($this->items ? $this->items->count() : 0)),
                'total_area_sqft' => (float) ($this->total_area_sqft ?? 0),
                'total_sqmt' => (float) $this->total_sqmt,
                'total_units' => (int) $this->total_units,
            ],
            
            'terms_conditions' => $this->terms_conditions,
            'bank_details' => $this->bank_details,
            'cover_letter_enclosures' => $this->cover_letter_enclosures,
            'pdf_path' => $this->pdf_path ? asset($this->pdf_path) : null,
            
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toIso8601String() : null,
        ];
    }
}
