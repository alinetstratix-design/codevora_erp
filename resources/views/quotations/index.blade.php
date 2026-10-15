@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Quotations</h5>
        <a href="{{ route('quotations.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Create Quotation</a>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('quotations.index') }}" class="row g-2 mb-3">
            <div class="col-md-5">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search quote no, client or project">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    @foreach(\App\Constants\QuotationStatus::all() as $statusOption)
                        <option value="{{ $statusOption }}" @selected(request('status') === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-outline-secondary"><i class="bi bi-search"></i> Filter</button>
                <a href="{{ route('quotations.index') }}" class="btn btn-link">Reset</a>
            </div>
        </form>
        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Quote No</th>
                        <th>Date</th>
                        <th>Customer</th>
                        <th>Project</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quote)
                    <tr>
                        <td><strong>{{ $quote->quotation_number }}</strong></td>
                        <td>{{ \Carbon\Carbon::parse($quote->quotation_date)->format('d-M-Y') }}</td>
                        <td>{{ $quote->customer->name ?? 'Unknown' }}</td>
                        <td>{{ $quote->project_name ?? '-' }}</td>
                        <td>₹{{ number_format($quote->grand_total, 2) }}</td>
                        <td>
                            <span class="badge {{ in_array($quote->status, ['Approved', 'Accepted']) ? 'bg-success' : ($quote->status == 'Draft' ? 'bg-secondary' : ($quote->status == 'Rejected' ? 'bg-danger' : 'bg-primary')) }}">
                                {{ $quote->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('quotations.show', $quote->id) }}" class="btn btn-sm btn-outline-info"><i class="bi bi-eye"></i></a>
                            @if($quote->pdf_path)
                                <a href="{{ route('quotations.downloadPdf', $quote->id) }}" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-pdf"></i></a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No quotations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $quotations->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
