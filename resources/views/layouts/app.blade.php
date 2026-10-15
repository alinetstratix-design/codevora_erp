<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Codevora AI | @yield('title', 'Dashboard')</title>

    <!-- Google Font: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- Custom Premium Style -->
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Inter', sans-serif;
            color: #334155;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
        }
        .sidebar {
            background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
            min-height: 100vh;
            color: #e2e8f0;
            box-shadow: 4px 0 15px rgba(0,0,0,0.05);
        }
        .sidebar a {
            color: #94a3b8;
            text-decoration: none;
            padding: 12px 18px;
            display: block;
            border-radius: 8px;
            margin-bottom: 4px;
            transition: all 0.3s ease;
            font-weight: 500;
        }
        .sidebar a:hover, .sidebar a.active {
            color: #ffffff;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }
        .content-wrapper {
            padding: 24px;
        }
        .navbar {
            background-color: rgba(255, 255, 255, 0.9) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
            border-bottom: 1px solid #f1f5f9;
        }
    </style>
    @stack('styles')
</head>
<body>
<div class="d-flex">
    <!-- Main Sidebar Container -->
    <aside class="sidebar p-3" style="width: 250px;">
        <a href="{{ auth()->user()?->isSuperAdmin() ? route('platform.index') : route('dashboard') }}" class="d-flex flex-column mb-3 text-white text-decoration-none">
            <div class="bg-white p-2 rounded-2 mb-2 text-center">
                <img src="{{ asset('images/framequote-logo.png') }}" alt="FrameQuote Logo" style="height: 32px; object-fit: contain;">
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge bg-primary bg-opacity-25 text-info" style="font-size: 10px;">By Codevora</span>
                <small class="text-white-50 text-truncate" style="max-width: 130px;">{{ auth()->user()?->company?->name ?? 'Platform' }}</small>
            </div>
        </a>
        <hr class="text-white-50">
        <ul class="nav nav-pills flex-column mb-auto mt-3">
            @if(auth()->user()?->isSuperAdmin())
            <li>
                <a href="{{ route('platform.index') }}" class="nav-link {{ request()->routeIs('platform.index', 'platform.companies.*') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            @php($pendingDemo = \App\Models\DemoRequest::where('status', 'pending')->count())
            <li>
                <a href="{{ route('platform.demo-requests.index') }}" class="nav-link {{ request()->routeIs('platform.demo-requests.*') ? 'active' : '' }}">
                    <i class="bi bi-person-check me-2"></i> Trial Requests
                    @if($pendingDemo > 0)<span class="badge bg-warning text-dark ms-1">{{ $pendingDemo }}</span>@endif
                </a>
            </li>
            @else
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2 me-2"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="{{ route('quotations.index') }}" class="nav-link {{ request()->routeIs('quotations.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt me-2"></i> Quotations
                </a>
            </li>
            <li>
                <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                    <i class="bi bi-people me-2"></i> Customers
                </a>
            </li>
            @if(in_array(auth()->user()?->role, ['Admin', 'Manager']))
            <li>
                <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam me-2"></i> Products
                </a>
            </li>
            <li>
                <a href="{{ route('materials.index') }}" class="nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                    <i class="bi bi-layers me-2"></i> Master Data
                </a>
            </li>
            <li>
                <a href="{{ route('company.edit') }}" class="nav-link {{ request()->routeIs('company.*') ? 'active' : '' }}">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </li>
            @endif
            @if(auth()->user()?->role === 'Admin')
            <li>
                <a href="{{ route('team.index') }}" class="nav-link {{ request()->routeIs('team.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear me-2"></i> Team
                </a>
            </li>
            <li>
                <a href="{{ route('activity.index') }}" class="nav-link {{ request()->routeIs('activity.*') ? 'active' : '' }}">
                    <i class="bi bi-clock-history me-2"></i> Activity
                </a>
            </li>
            @endif
            @endif
        </ul>
    </aside>

    <!-- Content Wrapper -->
    <div class="flex-grow-1">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light px-4">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">@yield('title', 'Dashboard')</span>
                
                <div class="d-flex">
                    <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name ?? 'Admin' }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><span class="dropdown-item-text small text-muted">{{ auth()->user()->email ?? '' }}</span></li>
                            <li><a class="dropdown-item" href="{{ route('account.password.edit') }}"><i class="bi bi-key me-1"></i> Change password</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-1"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="content-wrapper">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

@if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if(session('temp_password'))
                <div class="alert alert-warning" role="alert">
                    <strong>Temporary password (shown once):</strong>
                    <div>Email: <code>{{ session('temp_email') }}</code></div>
                    <div>Password: <code>{{ session('temp_password') }}</code></div>
                    <div class="small">Share it securely. The user must choose a new password at first sign-in.</div>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @php($accessLevel = (auth()->user() && !auth()->user()->isSuperAdmin()) ? auth()->user()->company?->accessLevel() : 'full')
            @if($accessLevel === 'read_only')
                <div class="alert alert-warning mb-3"><strong>Read-only mode.</strong> Your subscription has ended. You can view your data, but creating or editing is disabled until you renew. Contact <a href="mailto:{{ config('app.support_email') }}">{{ config('app.support_email') }}</a>.</div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

<!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
