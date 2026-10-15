<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionAuditLog extends Model
{
    protected $table = 'subscription_audit_logs';

    protected $fillable = ['company_id', 'actor_id', 'action', 'before', 'after', 'note'];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
