<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\CompanyScope);
    }

    protected $fillable = [
        'company_id',
        'product_code',
        'name',
        'material',
        'category',
        'opening_type',
        'profile_brand',
        'profile_series',
        'glass_type',
        'glass_thickness',
        'mesh_type',
        'hardware_brand',
        'default_image',
        'uom',
        'unit',
        'base_rate',
        'description',
        'status',
        'profile_details',
        'accessories_details',
        'profile_weight_per_mtr',
        'profile_rate_per_kg',
        'glass_rate_per_sqft',
        'hardware_kit_cost',
        'wastage_percent',
        'profit_margin_percent',
        'labor_rate_per_sqft',
        'profile_calc_formula',
    ];

    protected $casts = [
        'profile_details' => 'array',
        'accessories_details' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function components()
    {
        return $this->hasMany(BomRule::class, 'product_id');
    }

    public function designs()
    {
        return $this->belongsToMany(Design::class, 'product_design');
    }
}
