@extends('layouts.app')

@section('title', 'ERP Dashboard')

@section('content')
<!-- Quick Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary"><i class="bi bi-speedometer2"></i> Fenestration ERP Dashboard</h4>
    <div class="btn-group">
        <a href="{{ route('quotations.create') }}" class="btn btn-primary"><i class="bi bi-plus-circle"></i> New Quotation</a>
        <a href="{{ route('customers.create') }}" class="btn btn-outline-success"><i class="bi bi-person-plus"></i> Add Customer</a>
        <a href="{{ route('products.create') }}" class="btn btn-outline-info"><i class="bi bi-box-seam"></i> Add Product</a>
        <a href="{{ route('company.edit') }}" class="btn btn-outline-secondary"><i class="bi bi-gear"></i> Settings</a>
    </div>
</div>

<!-- Live Workflow & Validation KPI Cards -->
<div class="row g-3 mb-4">
    <!-- Total Customers -->
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-primary shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Total Customers</h6>
                        <h3 class="mb-0 fw-bold">{{ number_format($totalCustomers) }}</h3>
                    </div>
                    <i class="bi bi-people fs-1 opacity-50"></i>
                </div>
            </div>
            <a href="{{ route('customers.index') }}" class="card-footer text-white text-decoration-none small d-flex justify-content-between">
                <span>Manage Customers</span><i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Total Quotations -->
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-info shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Total Quotations</h6>
                        <h3 class="mb-0 fw-bold">{{ number_format($totalQuotations) }}</h3>
                    </div>
                    <i class="bi bi-file-earmark-text fs-1 opacity-50"></i>
                </div>
            </div>
            <a href="{{ route('quotations.index') }}" class="card-footer text-white text-decoration-none small d-flex justify-content-between">
                <span>View Quotations</span><i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Monthly Sales -->
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-success shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Monthly Sales</h6>
                        <h3 class="mb-0 fw-bold">₹{{ number_format($monthlySales, 2) }}</h3>
                    </div>
                    <i class="bi bi-currency-rupee fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer text-white small">Current Month Revenue</div>
        </div>
    </div>

    <!-- Pipeline Value -->
    <div class="col-lg-3 col-md-6">
        <div class="card text-white bg-dark shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase mb-1 opacity-75">Pipeline Value</h6>
                        <h3 class="mb-0 fw-bold">₹{{ number_format($totalQuotationValue, 2) }}</h3>
                    </div>
                    <i class="bi bi-graph-up-arrow fs-1 opacity-50"></i>
                </div>
            </div>
            <div class="card-footer text-white small">Cumulative Pipeline</div>
        </div>
    </div>

    <!-- Draft Quotes -->
    <div class="col-lg-2 col-md-4">
        <div class="card border-secondary shadow-sm h-100">
            <div class="card-body p-2 text-center">
                <h6 class="text-muted small mb-1">Draft</h6>
                <h4 class="mb-0 text-secondary fw-bold">{{ number_format($draftQuotations) }}</h4>
            </div>
        </div>
    </div>

    <!-- Pending Validation -->
    <div class="col-lg-2 col-md-4">
        <div class="card border-warning shadow-sm h-100">
            <div class="card-body p-2 text-center">
                <h6 class="text-muted small mb-1">Pending Validation</h6>
                <h4 class="mb-0 text-warning fw-bold">{{ number_format($pendingFollowups) }}</h4>
            </div>
        </div>
    </div>

    <!-- Ready for Approval -->
    <div class="col-lg-2 col-md-4">
        <div class="card border-info shadow-sm h-100">
            <div class="card-body p-2 text-center">
                <h6 class="text-muted small mb-1">Ready For Approval</h6>
                <h4 class="mb-0 text-info fw-bold">{{ number_format($draftQuotations) }}</h4>
            </div>
        </div>
    </div>

    <!-- Approved Quotes -->
    <div class="col-lg-2 col-md-4">
        <div class="card border-success shadow-sm h-100">
            <div class="card-body p-2 text-center">
                <h6 class="text-muted small mb-1">Approved</h6>
                <h4 class="mb-0 text-success fw-bold">{{ number_format($approvedQuotations) }}</h4>
            </div>
        </div>
    </div>

    <!-- Rejected Quotes -->
    <div class="col-lg-2 col-md-4">
        <div class="card border-danger shadow-sm h-100">
            <div class="card-body p-2 text-center">
                <h6 class="text-muted small mb-1">Rejected</h6>
                <h4 class="mb-0 text-danger fw-bold">{{ number_format($rejectedQuotations) }}</h4>
            </div>
        </div>
    </div>

    <!-- Converted to Order -->
    <div class="col-lg-2 col-md-4">
        <div class="card border-primary shadow-sm h-100">
            <div class="card-body p-2 text-center">
                <h6 class="text-muted small mb-1">Converted Order</h6>
                <h4 class="mb-0 text-primary fw-bold">{{ number_format($approvedQuotations) }}</h4>
            </div>
        </div>
    </div>
</div>

<!-- Recent Tables Split -->
<div class="row g-4">
    <!-- Recent Quotations -->
    <div class="col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary fw-bold"><i class="bi bi-clock-history"></i> Recent Quotations</h6>
                <a href="{{ route('quotations.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Quote No.</th>
                                <th>Client / Project</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentQuotations as $q)
                            <tr>
                                <td class="fw-bold">{{ $q->quotation_number ?? $q->quote_no }}</td>
                                <td>
                                    <div>{{ $q->client_name }}</div>
                                    <small class="text-muted">{{ $q->project_name }}</small>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($q->quotation_date ?? $q->date)->format('d M Y') }}</td>
                                <td class="fw-bold text-success">₹{{ number_format($q->grand_total, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ $q->status ?? 'Draft' }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('quotations.show', $q->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> View</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No recent quotations found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Customers -->
    <div class="col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-success fw-bold"><i class="bi bi-person-lines-fill"></i> Recent Customers</h6>
                <a href="{{ route('customers.index') }}" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Company</th>
                                <th>Phone</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentCustomers as $c)
                            <tr>
                                <td class="fw-bold text-primary">{{ $c->customer_code }}</td>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->company_name ?? '-' }}</td>
                                <td>{{ $c->phone ?? '-' }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-4">No recent customers found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
