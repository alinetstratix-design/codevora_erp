@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-receipt"></i> Quotations</h5>
        <a href="{{ route('quotations.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Create Quotation</a>
    </div>
    <div class="card-body">
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
                            <span class="badge {{ $quote->status == 'Approved' ? 'bg-success' : ($quote->status == 'Draft' ? 'bg-secondary' : 'bg-primary') }}">
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
