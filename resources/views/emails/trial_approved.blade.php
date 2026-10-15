<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f8fafc; color: #1e293b; margin: 0; padding: 20px; }
  .wrapper { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; }
  .header { background: #071529; padding: 24px; text-align: center; }
  .header h2 { color: #ffffff; margin: 8px 0 0; font-size: 22px; font-weight: 700; }
  .badge { display: inline-block; background: #0a66c2; color: #ffffff; font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: uppercase; margin-bottom: 8px; }
  .content { padding: 30px 28px; line-height: 1.6; font-size: 15px; }
  .cred-box { background: #f0f7ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 20px; margin: 20px 0; }
  .cred-item { margin-bottom: 10px; }
  .cred-label { font-size: 12px; font-weight: 700; color: #0369a1; text-transform: uppercase; }
  .cred-val { font-size: 16px; font-weight: 700; color: #0f172a; font-family: monospace; }
  .btn { display: inline-block; background: #0a66c2; color: #ffffff !important; font-weight: 700; text-decoration: none; padding: 14px 28px; border-radius: 8px; font-size: 15px; text-align: center; margin: 15px 0; }
  .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 13px; color: #64748b; border-top: 1px solid #e2e8f0; }
</style>
</head>
<body>

<div class="wrapper">
  <div class="header">
    <span class="badge">3-Day Free Trial Approved</span>
    <h2>Welcome to FrameQuote</h2>
    <p style="color: #94a3b8; font-size: 13px; margin: 4px 0 0;">Powered by Codevora Technologies</p>
  </div>

  <div class="content">
    <p>Dear <strong>{{ $user->name }}</strong>,</p>
    <p>Your request for a <strong>3-Day Full-Access Free Trial</strong> for <strong>{{ $company->name }}</strong> has been approved by the platform administrator!</p>

    <div class="cred-box">
      <div class="cred-item">
        <div class="cred-label">Login Portal URL</div>
        <div class="cred-val"><a href="{{ $loginUrl }}" style="color: #0a66c2;">{{ $loginUrl }}</a></div>
      </div>
      <div class="cred-item">
        <div class="cred-label">Username / Registered Email</div>
        <div class="cred-val">{{ $user->email }}</div>
      </div>
      <div class="cred-item">
        <div class="cred-label">Temporary Password</div>
        <div class="cred-val">{{ $plainPassword }}</div>
      </div>
      <div class="cred-item" style="margin-bottom: 0;">
        <div class="cred-label">Trial Expiry Date</div>
        <div class="cred-val" style="color: #d97706;">{{ $company->trial_ends_at ? $company->trial_ends_at->format('d M Y, h:i A') : '3 Days' }}</div>
      </div>
    </div>

    <div style="text-align: center;">
      <a href="{{ $loginUrl }}" class="btn">Sign In to Quotation Studio &rarr;</a>
    </div>

    <p style="font-size: 13.5px; color: #64748b;">
      <em>Note: For security reasons, you will be prompted to set your personal permanent password on your first sign in.</em>
    </p>

    <p>With this trial, you have full access to:
      <ul>
        <li>uPVC &amp; Aluminium Window/Door Quotation Studio</li>
        <li>Live BOM Costing &amp; Profile Weight Estimation</li>
        <li>EvA-Standard CAD Elevation SVG Diagrams</li>
        <li>Client PDF Generation &amp; 1-Click WhatsApp Sharing</li>
      </ul>
    </p>
  </div>

  <div class="footer">
    <p>&copy; {{ date('Y') }} FrameQuote by Codevora Technologies. All rights reserved.</p>
    <p>Need help or want to upgrade? Contact us at <a href="mailto:support@codevoraai.com" style="color: #0a66c2;">support@codevoraai.com</a></p>
  </div>
</div>

</body>
</html>
