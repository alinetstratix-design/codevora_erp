<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
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
            'product_id' => $this->product_id,
            'item_code' => $this->item_code ?? $this->position,
            'position' => $this->position,
            'system_name' => $this->system_name ?? $this->product_name,
            'profile_system' => $this->profile_system ?? 'CORA - 60MM CASEMENT SERIES',
            'qty' => (int) ($this->qty ?? $this->quantity ?? 1),
            'dimension_w' => (float) ($this->dimension_w ?? $this->width ?? 0),
            'dimension_h' => (float) ($this->dimension_h ?? $this->height ?? 0),
            'unit' => $this->unit ?? 'mm',
            'area' => (float) $this->area,
            'weight_kg' => (float) $this->weight_kg,
            'profile_color' => $this->profile_color,
            'handle_type' => $this->handle_type,
            'hardware_color' => $this->hardware_color,
            'mesh_type' => $this->mesh_type,
            'glass_type' => $this->glass_type,
            'remarks' => $this->remarks ?? $this->notes,
            'drawing_type' => $this->drawing_type,
            'drawing_url' => $this->drawing_url,
            
            'financials' => [
                'rate' => (float) $this->rate,
                'unit_price' => (float) $this->unit_price,
                'value_per_sqft' => (float) $this->value_per_sqft,
                'material_cost' => (float) $this->material_cost,
                'glass_cost' => (float) $this->glass_cost,
                'total_cost' => (float) ($this->total_cost ?? $this->amount),
                'amount' => (float) ($this->amount ?? $this->total_cost),
            ],
            
            'profile_details' => $this->profile_details,
            'accessories_details' => $this->accessories_details,
            'drawing_metadata' => $this->drawing_metadata,
            'technical_specs' => $this->technical_specs,
            'sizes' => $this->whenLoaded('sizes'),
        ];
    }
}
