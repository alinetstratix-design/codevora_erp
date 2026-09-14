<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'product_id',
        'design_id',
        'product_name',
        'position',
        'profile_brand',
        'profile_series',
        'opening_type',
        'glass_type',
        'glass_thickness',
        'hardware_brand',
        'mesh_type',
        'color',
        'rate',
        'amount',
        'notes',
        'sort_order',

        // Old fields retained for safety and SQLite limitations
        'width',
        'height',
        'area',
        'quantity',
        'unit',
        'system_name',
        'profile_color',
        'handle_type',
        'hardware_color',
        'drawing_type',
        'drawing_url',
        'material_cost',
        'glass_cost',
        'technical_specs',
        'drawing_image',

        // EvA Item Fields
        'item_code',
        'profile_system',
        'weight_kg',
        'value_per_sqft',
        'unit_price',
        'profile_details',
        'accessories_details',
        'drawing_metadata',
    // Costing Fields from Phase C
        'bom_cost',
        'additional_cost',
        'cost_basis',
        'margin_percent',
        'margin_amount',
        'line_total',
    ];

    protected $casts = [
        'profile_details' => 'array',
        'accessories_details' => 'array',
        'drawing_metadata' => 'array',
        'technical_specs' => 'array',
    ];

    public function quotation()
    {
        return $this->belongsTo(Quotation::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }

    public function sizes()
    {
        return $this->hasMany(QuotationItemSize::class);
    }

    public function boms()
    {
        return $this->hasMany(QuotationItemBom::class);
    }
}
