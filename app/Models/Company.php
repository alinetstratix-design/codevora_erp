<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name',
        'is_active',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'max_users',
        'expiry_reminder_stage',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'max_users' => 'integer',
    ];

    public function isTrialActive(): bool
    {
        if ($this->subscription_status !== 'Trial') {
            return false;
        }

        return $this->trial_ends_at ? $this->trial_ends_at->isFuture() : false;
    }

    public function isSubscriptionActive(): bool
    {
        if ($this->subscription_status !== 'Active') {
            return false;
        }

        return $this->subscription_ends_at ? $this->subscription_ends_at->isFuture() : true;
    }

    public function hasAccess(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        return $this->isTrialActive() || $this->isSubscriptionActive();
    }

    /**
     * The date this company's current access ends: trial end while on Trial, plan end otherwise.
     */
    public function expiryDate()
    {
        return $this->subscription_status === 'Trial' ? $this->trial_ends_at : $this->subscription_ends_at;
    }

    /**
     * Expired, but still inside the grace window: data is visible, changes are blocked.
     */
    public function inGracePeriod(): bool
    {
        if (!$this->is_active || !in_array($this->subscription_status, ['Trial', 'Active'], true)) {
            return false;
        }

        $expiry = $this->expiryDate();

        if (!$expiry || $expiry->isFuture()) {
            return false;
        }

        return $expiry->copy()->addDays((int) config('subscription.grace_days', 3))->isFuture();
    }

    /**
     * full = normal access, read_only = grace period, locked = no access.
     */
    public function accessLevel(): string
    {
        if ($this->hasAccess()) {
            return 'full';
        }

        return $this->inGracePeriod() ? 'read_only' : 'locked';
    }

    public function daysRemaining(): int
    {
        $expiry = $this->subscription_status === 'Trial' ? $this->trial_ends_at : $this->subscription_ends_at;

        if (!$expiry) {
            return 999;
        }

        if ($expiry->isPast()) {
            return 0;
        }

        return (int) now()->diffInDays($expiry) + 1;
    }

    public function getSubscriptionBadge(): array
    {
        if (!$this->is_active) {
            return ['label' => 'Deactivated', 'class' => 'bg-danger text-white'];
        }

        if ($this->isTrialActive()) {
            $days = $this->daysRemaining();
            return ['label' => "Trial ({$days}d left)", 'class' => 'bg-warning text-dark'];
        }

        if ($this->isSubscriptionActive()) {
            return ['label' => 'Active', 'class' => 'bg-success text-white'];
        }

        return ['label' => 'Expired', 'class' => 'bg-danger text-white'];
    }

    public function seatLimit(): int
    {
        return (int) ($this->max_users ?: 3);
    }

    /**
     * Only Active members use a seat. Deactivated members free theirs.
     */
    public function activeSeatCount(): int
    {
        return $this->users()->where('status', 'Active')->count();
    }

    public function seatsFull(): bool
    {
        return $this->activeSeatCount() >= $this->seatLimit();
    }

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

    public function subscriptionAuditLogs()
    {
        return $this->hasMany(SubscriptionAuditLog::class, 'company_id')->latest();
    }

    public function demoRequests()
    {
        return $this->hasMany(DemoRequest::class, 'company_id');
    }
}
