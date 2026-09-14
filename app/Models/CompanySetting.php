<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'company_name',
        'logo',
        'gst_number',
        'address',
        'phone',
        'email',
        'website',
        'gstin',
        'bank_details',
        'terms_conditions',
        'installation_prerequisites',
        'authorized_signature'
    ];

    protected $casts = [
        'bank_details' => 'array',
        'terms_conditions' => 'array',
        'installation_prerequisites' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
