<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
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
    ];

    protected $casts = [
        'profile_details' => 'array',
        'accessories_details' => 'array',
    ];
}
