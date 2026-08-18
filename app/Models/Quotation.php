<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_number',
        'quotation_date',
        'valid_until',
        'customer_id',
        'company_setting_id',
        'project_name',
        'project_location',
        'sales_person',
        'remarks',
        'subtotal',
        'discount',
        'transportation',
        'installation',
        'gst',
        'grand_total',
        'amount_in_words',
        'status',
        'pdf_path',
        
        // Retained old fields for safety if needed
        'client_name',
        'opportunity_no',
        'address',
        'material_value',
        'net_value',
        'total_glass_cost',
        'installation_cost_rate',
        'freight_charges_rate',
        'discount_percent',
        'tax_percent',
        'tax_amount',
        'additional_charges',
        'total_sqmt',
        'total_units',
        'terms_conditions',
        'bank_details',
        
        // EvA Summary fields
        'no_of_components',
        'total_area_sqft',
        'basic_value',
        'total_project_cost',
        'avg_price_sqft_ex_gst',
        'avg_price_sqft_inc_gst',
        'cover_letter_enclosures',
    ];

    protected $casts = [
        'date' => 'date',
        'valid_till' => 'date',
        'terms_conditions' => 'array',
        'bank_details' => 'array',
        'cover_letter_enclosures' => 'array',
    ];

    protected static function booted()
    {
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
}
