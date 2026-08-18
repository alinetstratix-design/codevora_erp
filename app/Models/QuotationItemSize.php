<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotationItemSize extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_item_id',
        'width',
        'height',
        'unit',
        'quantity',
        'area',
        'amount'
    ];

    public function item()
    {
        return $this->belongsTo(QuotationItem::class, 'quotation_item_id');
    }
}
