<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sign in') | Codevora AI</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">
<div class="container" style="max-width: 440px;">
    <div class="text-center mb-4">
        <a href="/" class="d-inline-flex flex-column align-items-center text-decoration-none">
            <img src="{{ asset('images/framequote-logo.png') }}" alt="FrameQuote Logo" style="height: 48px; object-fit: contain;">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 mt-2 fw-semibold" style="font-size: 11px;">
                <i class="bi bi-shield-check me-1"></i> By Codevora Technologies
            </span>
        </a>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
    <p class="text-center mt-3 small"><a href="{{ route('login') }}">Back to sign in</a></p>
</div>
</body>
</html>
