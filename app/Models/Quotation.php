<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number', // Or maybe this should be unfillable too if generated
        'quotation_date',
        'valid_until',
        'customer_id',
        'project_name',
        'project_location',
        'sales_person',
        'remarks',
        'status',
        
        // Retained old fields for safety if needed
        'client_name',
        'opportunity_no',
        'address',
        'terms_conditions',
        'bank_details',

        // Costing Fields from Phase C
        'bom_cost_total',
        'additional_cost_total',
        'cost_basis_total',
        'margin_total',
        'taxable_amount',
        'no_of_components',
        'total_area_sqft',
        'basic_value',
        'subtotal',
        'discount',
        'discount_percent',
        'transportation',
        'installation',
        'tax_percent',
        'tax_amount',
        'gst',
        'gst_percent',
        'grand_total',
        'amount_in_words',
        'pdf_path',
        'total_project_cost',
        'avg_price_sqft_ex_gst',
        'avg_price_sqft_inc_gst',
        'company_id',
        'company_setting_id',
        'cover_letter_enclosures',
    ];

    protected $guarded = ['id']; // Everything else is guarded against mass assignment

    protected $casts = [
        'quotation_date' => 'date',
        'valid_until' => 'date',
        'date' => 'date',
        'valid_till' => 'date',
        'terms_conditions' => 'array',
        'bank_details' => 'array',
        'cover_letter_enclosures' => 'array',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new \App\Scopes\CompanyScope);

        static::saved(function ($quotation) {
            \Illuminate\Support\Facades\Cache::forget('dashboard.stats');
        });

        static::deleted(function ($quotation) {
            \Illuminate\Support\Facades\Cache::forget('dashboard.stats');
        });
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function companySetting()
    {
        return $this->belongsTo(CompanySetting::class);
    }

    public function items()
    {
        return $this->hasMany(QuotationItem::class)->orderBy('sort_order', 'asc');
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
