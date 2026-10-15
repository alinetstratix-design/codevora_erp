@php($isTrial = (($company->subscription_status ?? '') === 'Trial'))
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $isTrial ? 'Trial Expired' : 'Subscription Expired' }} | FrameQuote by Codevora</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<style>
  body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background: linear-gradient(135deg, #071529 0%, #0d213f 50%, #0a66c2 100%);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
    color: #1e293b;
  }
  .card-expired {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35);
    max-width: 540px;
    width: 100%;
    overflow: hidden;
    text-align: center;
    border: 1px solid rgba(255, 255, 255, 0.15);
  }
  .card-header-banner {
    background: #fff1f2;
    padding: 24px 20px;
    border-bottom: 1px solid #ffe4e6;
  }
  .expired-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: #ffe4e6;
    color: #e11d48;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 30px;
    margin-bottom: 12px;
  }
  .card-body-content {
    padding: 32px 30px;
  }
  .company-pill {
    background: #f1f5f9;
    padding: 6px 14px;
    border-radius: 30px;
    font-size: 13px;
    font-weight: 700;
    color: #334155;
    display: inline-block;
    margin-bottom: 16px;
  }
  .features-retained {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    text-align: left;
    font-size: 13.5px;
    margin: 20px 0;
  }
  .features-retained ul {
    margin: 0;
    padding-left: 20px;
  }
  .features-retained li {
    margin-bottom: 6px;
  }
  .btn-whatsapp {
    background: #25d366;
    color: #ffffff;
    font-weight: 700;
    border: none;
  }
  .btn-whatsapp:hover {
    background: #20bd5a;
    color: #ffffff;
  }
</style>
</head>
<body>

<div class="card-expired">
  <div class="card-header-banner">
    <div class="mb-2">
      <img src="{{ asset('images/framequote-logo.png') }}" alt="FrameQuote" style="height: 40px; margin: 0 auto;">
    </div>
    <div class="expired-icon">
      <i class="bi bi-hourglass-bottom"></i>
    </div>
    <h4 class="fw-bold text-danger mb-1">{{ $isTrial ? 'Your 3-Day Free Trial Has Ended' : 'Your Subscription Has Ended' }}</h4>
    <p class="text-muted small mb-0">{{ $isTrial ? 'Trial expired for your workspace' : 'Subscription expired for your workspace' }}</p>
  </div>

  <div class="card-body-content">
    <div class="company-pill">
      <i class="bi bi-buildings me-1"></i> {{ $company->name ?? 'Your Company' }}
    </div>

    <p class="text-secondary" style="font-size: 14.5px; line-height: 1.6;">
      @if($isTrial)
        Thank you for evaluating <strong>FrameQuote</strong>. To continue creating quotations, downloading PDFs, and utilizing the BOM estimation engine, please upgrade your subscription.
      @else
        Your <strong>FrameQuote</strong> subscription has ended. Renew to continue creating quotations, downloading PDFs, and using the BOM estimation engine. Your data is still here.
      @endif
    </p>

    <div class="features-retained">
      <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-success me-1"></i> Your Data is Completely Safe:</div>
      <ul class="text-muted">
        <li>All created quotations &amp; customer records are preserved</li>
        <li>BOM configurations &amp; custom profiles remain intact</li>
        <li>Instant reactivation upon plan upgrade</li>
      </ul>
    </div>

    @php
      $waMessage = rawurlencode("Hi Codevora Team, access for company '" . ($company->name ?? 'My Company') . "' has ended. I want to upgrade to a paid subscription.");
    @endphp

    <div class="d-grid gap-2 mb-3">
      @if(config('app.support_whatsapp'))
      <a href="https://wa.me/{{ config('app.support_whatsapp') }}?text={{ $waMessage }}" target="_blank" rel="noopener" class="btn btn-whatsapp py-2 px-3 rounded-3 shadow-sm">
        <i class="bi bi-whatsapp me-2"></i> Upgrade via WhatsApp Support
      </a>
      @endif
      <a href="mailto:{{ config('app.support_email') }}?subject=Upgrade%20Subscription%20-%20{{ rawurlencode($company->name ?? '') }}" class="btn btn-outline-primary py-2 px-3 rounded-3">
        <i class="bi bi-envelope me-2"></i> Email Sales Team
      </a>
    </div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-link text-muted small text-decoration-none">
        <i class="bi bi-box-arrow-right me-1"></i> Sign Out from Account
      </button>
    </form>
  </div>
</div>

</body>
</html>
