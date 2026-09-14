<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FQMS | @yield('title', 'Dashboard')</title>

    <!-- Google Font: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    
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
        <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
            <span class="fs-4 fw-bold"><i class="bi bi-box"></i> FQMS</span>
        </a>
        <hr class="text-white-50">
        <ul class="nav nav-pills flex-column mb-auto mt-3">
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
            <hr class="text-white-50">
            <li>
                <a href="{{ route('company.edit') }}" class="nav-link {{ request()->routeIs('company.*') ? 'active' : '' }}">
                    <i class="bi bi-gear me-2"></i> Settings
                </a>
            </li>
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

            @yield('content')
        </main>
    </div>
</div>

<!-- Bootstrap 5 Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
