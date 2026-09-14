<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BomRule extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\CompanyScope);
    }

    protected $fillable = [
        'company_id',
        'product_id',
        'design_id',
        'material_id',
        'component_role',
        'rule_type',
        'rule_definition',
        'condition_definition',
        'unit',
        'formula',
        'condition',
        'sort_order',
    ];

    protected $casts = [
        'rule_definition' => 'array',
        'condition_definition' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function material()
    {
        return $this->belongsTo(Material::class);
    }

    public function design()
    {
        return $this->belongsTo(Design::class);
    }
}
