<!DOCTYPE html>
<html>
<body style="font-family: Arial, sans-serif; color: #1d2733; line-height: 1.5;">
    <h2 style="margin-bottom: 8px;">Hello {{ $company->name }},</h2>
    @if($stage === 'expired')
        <p>Your access to FrameQuote ended on <strong>{{ $expiry?->format('d M Y, h:i A') }}</strong>.</p>
        <p>Your data is safe. For the next <strong>{{ $graceDays }} days</strong> you can view it in read-only mode. Creating or editing quotations is disabled until you renew.</p>
    @else
        <p>Your {{ $company->subscription_status === 'Trial' ? 'free trial' : 'subscription' }} ends on <strong>{{ $expiry?->format('d M Y, h:i A') }}</strong>{{ $stage === '1d' ? ' (within 24 hours)' : '' }}.</p>
        <p>Renew now to keep creating quotations without interruption.</p>
    @endif
    <p><a href="{{ $loginUrl }}" style="background:#0a66c2;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;">Sign in to FrameQuote</a></p>
    <p>Questions about renewal? Reply to this email or write to <a href="mailto:{{ $supportEmail }}">{{ $supportEmail }}</a>.</p>
</body>
</html>
