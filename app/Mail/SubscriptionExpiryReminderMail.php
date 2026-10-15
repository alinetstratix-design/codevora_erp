<?php

namespace App\Mail;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionExpiryReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company, public string $stage)
    {
    }

    public function build()
    {
        $expiry = $this->company->expiryDate();
        $daysLeft = ($expiry && $expiry->isFuture())
            ? max(1, (int) ceil(abs(now()->diffInHours($expiry)) / 24))
            : 0;

        switch ($this->stage) {
            case '7d':
                $subject = 'Your FrameQuote access ends in ' . $daysLeft . ($daysLeft === 1 ? ' day' : ' days');
                break;
            case '1d':
                $subject = 'Action needed: your FrameQuote access ends within 24 hours';
                break;
            default:
                $subject = 'Your FrameQuote access has ended';
        }

        return $this->subject($subject)
            ->view('emails.subscription_reminder')
            ->with([
                'company' => $this->company,
                'stage' => $this->stage,
                'expiry' => $this->company->expiryDate(),
                'graceDays' => (int) config('subscription.grace_days', 3),
                'supportEmail' => config('app.support_email'),
                'loginUrl' => route('login'),
            ]);
    }
}
