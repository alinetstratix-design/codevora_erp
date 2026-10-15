<?php

namespace App\Console\Commands;

use App\Constants\UserRole;
use App\Mail\SubscriptionExpiryReminderMail;
use App\Models\Company;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sends one reminder per stage (7 days, 1 day, expired) to each company's admins.
 * The stage is stored on the company, so a daily run never sends the same reminder twice.
 */
class SendSubscriptionReminders extends Command
{
    protected $signature = 'subscriptions:send-reminders';

    protected $description = 'Email company admins before trial or plan expiry, and once when access ends.';

    public function handle(): int
    {
        $sent = 0;

        Company::query()
            ->where('is_active', true)
            ->whereIn('subscription_status', ['Trial', 'Active'])
            ->chunkById(100, function ($companies) use (&$sent) {
                foreach ($companies as $company) {
                    $stage = $this->stageFor($company);

                    if ($stage === null || $company->expiry_reminder_stage === $stage) {
                        continue;
                    }

                    if ($this->notify($company, $stage)) {
                        $company->forceFill(['expiry_reminder_stage' => $stage])->save();
                        $sent++;
                    }
                }
            });

        $this->info("Reminders sent: {$sent}");

        return self::SUCCESS;
    }

    private function stageFor(Company $company): ?string
    {
        $expiry = $company->expiryDate();

        if (!$expiry) {
            return null;
        }

        if ($expiry->isPast()) {
            // Only announce the end shortly after it happens, not for companies that lapsed long ago.
            $graceDays = (int) config('subscription.grace_days', 3);

            return $expiry->diffInDays(now()) <= $graceDays + 1 ? 'expired' : null;
        }

        $hoursLeft = now()->diffInHours($expiry);

        if ($hoursLeft <= 24) {
            return '1d';
        }

        if ($hoursLeft <= 24 * 7) {
            return '7d';
        }

        return null;
    }

    private function notify(Company $company, string $stage): bool
    {
        $emails = User::where('company_id', $company->id)
            ->where('role', UserRole::ADMIN)
            ->where('status', 'Active')
            ->whereNotNull('email')
            ->pluck('email')
            ->all();

        if (empty($emails)) {
            return false;
        }

        try {
            Mail::to($emails)->send(new SubscriptionExpiryReminderMail($company, $stage));

            return true;
        } catch (Throwable $e) {
            // Stage is not saved, so the next daily run retries.
            Log::warning("Subscription reminder ({$stage}) failed for company {$company->id}: " . $e->getMessage());

            return false;
        }
    }
}
