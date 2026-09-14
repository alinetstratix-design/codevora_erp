<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FQMS ERP | Sign In</title>

    <!-- Google Font: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .login-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 32px 28px 24px;
            text-align: center;
            color: #ffffff;
        }
        .login-header h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 26px;
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }
        .login-body {
            padding: 32px 28px;
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            font-size: 14.5px;
        }
        .form-control:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
        }
        .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        .btn-primary:hover {
            background: #1d4ed8;
            border-color: #1d4ed8;
            transform: translateY(-1px);
        }
        .credentials-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px;
            font-size: 13px;
            color: #475569;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="mb-2">
            <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill"><i class="bi bi-box-seam me-1"></i> EVA ERP / FQMS</span>
        </div>
        <h2>Welcome Back</h2>
        <p class="text-slate-400 mb-0 small opacity-75">Sign in to access your enterprise dashboard</p>
    </div>

    <div class="login-body">
        @if(session('success'))
            <div class="alert alert-success py-2 px-3 small rounded-3 mb-3">
                <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 small rounded-3 mb-3">
                <i class="bi bi-exclamation-triangle me-1"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            
            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">Email Address</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', 'admin@codevora.com') }}" required autofocus placeholder="name@company.com">
                </div>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 rounded-start-3 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control border-start-0 ps-0" id="password" name="password" required placeholder="••••••••">
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mb-3 shadow-sm">
                <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
        </form>

        <div class="credentials-pill mt-3">
            <div class="fw-semibold text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Default Demo Credentials:</div>
            <div class="d-flex justify-content-between mb-1">
                <span>Email:</span>
                <code>admin@codevora.com</code>
            </div>
            <div class="d-flex justify-content-between">
                <span>Password:</span>
                <code>password123</code>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
