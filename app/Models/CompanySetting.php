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
        'authorized_signature',
        'default_tax_percent',
        'item_specs',
        'inclusions',
        'exclusions',
    ];

    protected $casts = [
        'bank_details' => 'array',
        'terms_conditions' => 'array',
        'installation_prerequisites' => 'array',
        'default_tax_percent' => 'float',
        'item_specs' => 'array',
        'inclusions' => 'array',
        'exclusions' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    /**
     * Settings for one company only. Never falls back to another company's row.
     */
    public static function forCompany(?int $companyId): ?self
    {
        if ($companyId === null) {
            return null;
        }

        return static::where('company_id', $companyId)->first();
    }
}
