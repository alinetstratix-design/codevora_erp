<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    use HasFactory;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\CompanyScope);
    }

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'type',
        'configuration',
        'preview_image',
        'svg_template',
        'is_active',
    ];

    protected $casts = [
        'configuration' => 'array',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_design');
    }

    public function bomRules()
    {
        return $this->hasMany(BomRule::class, 'design_id');
    }
}
