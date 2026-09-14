<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\CompanyScope);
    }

    protected $fillable = [
        'company_id',
        'customer_code',
        'name',
        'company_name',
        'type',
        'contact_person',
        'phone',
        'email',
        'gst_number',
        'location',
        'address',
        'city',
        'state',
        'pincode',
        'notes',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
