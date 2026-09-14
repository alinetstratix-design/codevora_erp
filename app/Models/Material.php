<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\CompanyScope);
    }

    protected $fillable = [
        'company_id',
        'sku',
        'name',
        'category',
        'uom',
        'cost',
        'waste_percent',
        'weight_per_mtr',
        'stock_length',
        'is_active',
        'properties',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'properties' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    // Helper method to get property
    public function getProperty($key, $default = null)
    {
        return $this->properties[$key] ?? $default;
    }
}
