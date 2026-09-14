<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'is_active'];

    public function users()
    {
        return $this->hasMany(User::class, 'company_id');
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'company_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'company_id');
    }

    public function materials()
    {
        return $this->hasMany(Material::class, 'company_id');
    }

    public function designs()
    {
        return $this->hasMany(Design::class, 'company_id');
    }

    public function bomRules()
    {
        return $this->hasMany(BomRule::class, 'company_id');
    }

    public function quotations()
    {
        return $this->hasMany(Quotation::class, 'company_id');
    }

    public function settings()
    {
        return $this->hasOne(CompanySetting::class, 'company_id');
    }
}
