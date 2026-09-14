<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItemBom extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_item_id',
        'material_id',
        'rule_type',
        'material_sku',
        'material_name',
        'unit_cost',
        'calculated_qty',
        'total_cost',
        'calculation_snapshot',
    ];

    protected $casts = [
        'calculation_snapshot' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(QuotationItem::class, 'quotation_item_id');
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }
}
