@extends('layouts.app')

@section('title', 'Customer Profile - ' . $customer->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0 text-primary"><i class="bi bi-person-badge"></i> {{ $customer->name }}</h4>
    <div>
        <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-warning"><i class="bi bi-pencil"></i> Edit Customer</a>
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Customers</a>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Customer Details Card -->
    <div class="col-md-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white font-bold text-primary">
                <i class="bi bi-info-circle"></i> Customer Information
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><td class="text-muted fw-bold" width="40%">Code:</td><td class="fw-bold text-primary">{{ $customer->customer_code }}</td></tr>
                    <tr><td class="text-muted fw-bold">Company:</td><td>{{ $customer->company_name ?? '-' }}</td></tr>
                    <tr><td class="text-muted fw-bold">Mobile:</td><td>{{ $customer->phone ?? '-' }}</td></tr>
                    <tr><td class="text-muted fw-bold">Email:</td><td>{{ $customer->email ?? '-' }}</td></tr>
                    <tr><td class="text-muted fw-bold">GSTIN:</td><td>{{ $customer->gst_number ?? '-' }}</td></tr>
                    <tr><td class="text-muted fw-bold">City/State:</td><td>{{ $customer->city ?? '-' }}, {{ $customer->state ?? '-' }}</td></tr>
                    <tr><td class="text-muted fw-bold">Address:</td><td>{{ $customer->address ?? '-' }}</td></tr>
                    <tr><td class="text-muted fw-bold">Status:</td><td><span class="badge bg-{{ $customer->status == 'Active' ? 'success' : 'secondary' }}">{{ $customer->status ?? 'Active' }}</span></td></tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Right Column: Quotation & Project History -->
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 text-primary fw-bold"><i class="bi bi-folder-check"></i> Project & Quotation History</h6>
                <a href="{{ route('quotations.create') }}?customer_id={{ $customer->id }}" class="btn btn-sm btn-primary"><i class="bi bi-plus"></i> New Quote for Customer</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Quote No.</th>
                                <th>Project Name</th>
                                <th>Date</th>
                                <th>Grand Total</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($quotations as $q)
                            <tr>
                                <td class="fw-bold">{{ $q->quotation_number ?? $q->quote_no }}</td>
                                <td>{{ $q->project_name }}</td>
                                <td>{{ \Carbon\Carbon::parse($q->quotation_date ?? $q->date)->format('d M Y') }}</td>
                                <td class="fw-bold text-success">₹{{ number_format($q->grand_total, 2) }}</td>
                                <td><span class="badge bg-secondary">{{ $q->status ?? 'Draft' }}</span></td>
                                <td class="text-end">
                                    <a href="{{ route('quotations.show', $q->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i> View</a>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No quotations associated with this customer yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
