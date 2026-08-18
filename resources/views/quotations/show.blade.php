@extends('layouts.app')

@section('title', 'Quotation Preview & Commercial Validation - ' . ($quotation->quotation_number ?? $quotation->quote_no))

@section('content')
@inject('reportBuilder', 'App\Services\QuotationReportBuilder')
@php
    $report = $reportBuilder->build($quotation);
@endphp

<!-- Notification Flash Alerts -->
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Sticky Top Action Bar for PDF & Actions -->
<div class="card shadow-sm mb-4 sticky-top bg-white py-2 px-3 border border-primary border-opacity-25 rounded-3" style="top: 10px; z-index: 1020;">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="text-primary mb-0 fw-bold">
                <i class="bi bi-file-earmark-pdf-fill"></i> Quotation #{{ $quotation->quotation_number ?? $quotation->quote_no }}
            </h5>
            <div class="small text-muted mt-1">
                Status: <span class="badge bg-secondary px-2 py-1">{{ $quotation->status ?? 'Draft' }}</span> |
                Validation: <span class="badge bg-{{ $validationResult->badgeColor }} px-2 py-1">{{ $validationResult->scorePercent }}% — {{ $validationResult->statusLabel }}</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back to Quotations</a>
            <a href="{{ route('quotations.previewPdf', $quotation->id) }}" target="_blank" class="btn btn-info text-white btn-sm px-3 fw-bold"><i class="bi bi-eye"></i> Preview PDF</a>
            <a href="{{ route('quotations.downloadPdf', $quotation->id) }}" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm"><i class="bi bi-file-pdf"></i> Download PDF</a>
            <a href="{{ route('quotations.showBom', $quotation->id) }}" class="btn btn-success btn-sm px-3 fw-bold"><i class="bi bi-list-check"></i> Factory BOM</a>
            
            @if($validationResult->canApprove)
                <form action="{{ route('quotations.approve', $quotation->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm shadow-sm px-3 fw-bold"><i class="bi bi-check2-circle"></i> Approve Quotation</button>
                </form>
            @else
                <button type="button" class="btn btn-primary btn-sm disabled" data-bs-toggle="tooltip" title="Validation Score must be 100% to approve">
                    <i class="bi bi-lock-fill"></i> Approve (Blocked)
                </button>
            @endif
        </div>
    </div>
</div>

<!-- Commercial Validation Rule Engine Summary Dashboard Card -->
<div class="card shadow-sm mb-4 border-{{ $validationResult->badgeColor }}">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 text-primary fw-bold"><i class="bi bi-shield-check"></i> Commercial Validation & Compliance Audit</h6>
        <span class="badge bg-{{ $validationResult->badgeColor }} px-3 py-2 fs-6">
            {{ $validationResult->scorePercent }}% Audit Score
        </span>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Left Side: Passed Checks Summary -->
            <div class="col-md-7 border-end">
                <h6 class="fw-bold text-success mb-3"><i class="bi bi-check-circle-fill"></i> Verified Commercial Checkpoints</h6>
                <div class="row g-2">
                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-1 text-success"><i class="bi bi-check-lg"></i> Customer Information Complete</li>
                            <li class="mb-1 text-success"><i class="bi bi-check-lg"></i> Pricing & Calculations Complete</li>
                            <li class="mb-1 text-success"><i class="bi bi-check-lg"></i> CAD Vector SVG Drawings Ready</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled mb-0 small">
                            <li class="mb-1 text-success"><i class="bi bi-check-lg"></i> Quotation PDF Ready</li>
                            <li class="mb-1 text-success"><i class="bi bi-check-lg"></i> Commercial Terms & Scope Ready</li>
                            <li class="mb-1 text-success"><i class="bi bi-check-lg"></i> Company & GST Details Ready</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Right Side: Failed / Missing Items -->
            <div class="col-md-5">
                <h6 class="fw-bold text-{{ count($validationResult->failedChecks) > 0 ? 'danger' : 'success' }} mb-3">
                    <i class="bi bi-{{ count($validationResult->failedChecks) > 0 ? 'exclamation-triangle-fill' : 'patch-check-fill' }}"></i> 
                    {{ count($validationResult->failedChecks) > 0 ? 'Missing / Required Action Items' : 'All Validation Checks Passed 100%' }}
                </h6>

                @if(count($validationResult->failedChecks) > 0)
                    <ul class="list-group list-group-flush small">
                        @foreach($validationResult->failedChecks as $failed)
                            <li class="list-group-item px-0 py-1 text-danger">
                                <i class="bi bi-x-circle me-1"></i> <strong>{{ $failed->label }}:</strong> {{ $failed->message }}
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="alert alert-success mb-0 py-2 small">
                        This quotation meets 100% of commercial compliance criteria and is ready for formal customer approval.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4 rounded-3 overflow-hidden">
    <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center py-2 px-3">
        <span class="fw-bold"><i class="bi bi-file-pdf-fill text-danger me-2"></i> Live PDF Document Viewer</span>
        <div>
            <a href="{{ route('quotations.previewPdf', $quotation->id) }}" target="_blank" class="btn btn-sm btn-outline-light me-2">
                <i class="bi bi-box-arrow-up-right me-1"></i> Open Fullscreen PDF
            </a>
            <a href="{{ route('quotations.downloadPdf', $quotation->id) }}" class="btn btn-sm btn-danger px-3">
                <i class="bi bi-download me-1"></i> Download PDF
            </a>
        </div>
    </div>
    <div class="card-body p-0" style="background-color: #525659;">
        <iframe src="{{ route('quotations.previewPdf', $quotation->id) }}" style="width: 100%; height: 950px; border: none;" title="Quotation PDF Preview"></iframe>
    </div>
</div>
@endsection
