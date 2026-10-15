<?php

namespace App\Services;

use App\Models\Company;
use App\Models\SubscriptionAuditLog;
use App\Models\User;
use DomainException;

/**
 * Every subscription change goes through here, so the rules and the audit trail stay in one place.
 */
class SubscriptionService
{
    public const TRIAL_DAYS = 3;

    public const PLAN_DAYS = 30;

    public function extendTrial(Company $company, ?User $actor = null): void
    {
        if ($company->subscription_status === 'Active' && $company->isSubscriptionActive()) {
            throw new DomainException('This company is on a paid plan. Use the 30-day extension instead of a trial extension.');
        }

        $before = $this->snapshot($company);

        $base = ($company->trial_ends_at && $company->trial_ends_at->isFuture())
            ? $company->trial_ends_at->copy()
            : now();
        $end = $base->copy()->addDays(self::TRIAL_DAYS);

        $company->update([
            'subscription_status' => 'Trial',
            'trial_ends_at' => $end,
            'subscription_ends_at' => $end,
            'is_active' => true,
            'expiry_reminder_stage' => null,
        ]);

        $this->record($company, 'trial_extended', $before, $this->snapshot($company), $actor);
    }

    public function activatePaid(Company $company, ?User $actor = null): void
    {
        $before = $this->snapshot($company);
        $action = $company->subscription_status === 'Active' ? 'plan_extended' : 'plan_activated';

        $base = ($company->subscription_ends_at && $company->subscription_ends_at->isFuture())
            ? $company->subscription_ends_at->copy()
            : now();

        $company->update([
            'subscription_status' => 'Active',
            'subscription_ends_at' => $base->copy()->addDays(self::PLAN_DAYS),
            'is_active' => true,
            'max_users' => max((int) $company->max_users, 10),
            'expiry_reminder_stage' => null,
        ]);

        $this->record($company, $action, $before, $this->snapshot($company), $actor);
    }

    public function suspend(Company $company, ?User $actor = null): void
    {
        $before = $this->snapshot($company);

        $company->update([
            'subscription_status' => 'Suspended',
            'is_active' => false,
        ]);

        $this->record($company, 'suspended', $before, $this->snapshot($company), $actor);
    }

    public function setActive(Company $company, bool $active, ?User $actor = null): void
    {
        $before = $this->snapshot($company);

        $company->update(['is_active' => $active]);

        $this->record($company, $active ? 'reactivated' : 'deactivated', $before, $this->snapshot($company), $actor);
    }

    public function snapshot(Company $company): array
    {
        return [
            'subscription_status' => $company->subscription_status,
            'is_active' => (bool) $company->is_active,
            'trial_ends_at' => optional($company->trial_ends_at)->toDateTimeString(),
            'subscription_ends_at' => optional($company->subscription_ends_at)->toDateTimeString(),
            'max_users' => (int) $company->max_users,
        ];
    }

    public function record(Company $company, string $action, array $before, array $after, ?User $actor = null, ?string $note = null): SubscriptionAuditLog
    {
        return SubscriptionAuditLog::create([
            'company_id' => $company->id,
            'actor_id' => $actor?->id,
            'action' => $action,
            'before' => $before,
            'after' => $after,
            'note' => $note,
        ]);
    }
}
