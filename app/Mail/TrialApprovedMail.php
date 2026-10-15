<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\DemoRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public DemoRequest $demoRequest;
    public Company $company;
    public User $user;
    public string $plainPassword;
    public string $loginUrl;

    public function __construct(DemoRequest $demoRequest, Company $company, User $user, string $plainPassword)
    {
        $this->demoRequest = $demoRequest;
        $this->company = $company;
        $this->user = $user;
        $this->plainPassword = $plainPassword;
        $this->loginUrl = url('/login');
    }

    public function build()
    {
        return $this->subject('Your FrameQuote 3-Day Free Trial Credentials')
            ->view('emails.trial_approved');
    }
}
