<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'product_code' => $this->product_code,
            'name' => $this->name,
            'material' => $this->material,
            'category' => $this->category,
            'opening_type' => $this->opening_type,
            'profile_brand' => $this->profile_brand,
            'profile_series' => $this->profile_series,
            'glass_type' => $this->glass_type,
            'glass_thickness' => $this->glass_thickness,
            'mesh_type' => $this->mesh_type,
            'hardware_brand' => $this->hardware_brand,
            'description' => $this->description,
            'uom' => $this->uom,
            'unit' => $this->unit,
            'base_rate' => (float) $this->base_rate,
            'default_image' => $this->default_image ? asset('storage/' . $this->default_image) : null,
            'status' => $this->status,
        ];
    }
}
