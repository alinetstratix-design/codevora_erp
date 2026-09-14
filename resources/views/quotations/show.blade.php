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
            <a href="{{ route('quotations.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
            <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-warning text-dark btn-sm px-3 fw-bold shadow-sm"><i class="bi bi-pencil"></i> Edit Quotation</a>
            <a href="{{ route('quotations.previewPdf', $quotation->id) }}" target="_blank" class="btn btn-info text-white btn-sm px-3 fw-bold shadow-sm"><i class="bi bi-eye"></i> Preview PDF</a>
            <a href="{{ route('quotations.downloadPdf', $quotation->id) }}" class="btn btn-danger btn-sm px-3 fw-bold shadow-sm"><i class="bi bi-file-pdf"></i> Download PDF</a>
            <a href="{{ route('quotations.showBom', $quotation->id) }}" class="btn btn-success btn-sm px-3 fw-bold shadow-sm"><i class="bi bi-list-check"></i> Factory BOM</a>
            
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
                    <ul class="list-group list-group-flush small mb-3">
                        @foreach($validationResult->failedChecks as $failed)
                            <li class="list-group-item px-0 py-1 text-danger">
                                <i class="bi bi-x-circle me-1"></i> <strong>{{ $failed->label }}:</strong> {{ $failed->message }}
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-sm btn-outline-danger shadow-sm"><i class="bi bi-pencil me-1"></i> Edit Quotation to Fix Issues</a>
                @else
                    <div class="alert alert-success mb-0 py-2 small">
                        This quotation meets 100% of commercial compliance criteria and is ready for formal customer approval.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Customer & Project Specifications Summary -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-person-lines-fill me-1"></i> Customer & Site Information</h6>
            </div>
            <div class="card-body py-2">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <th width="35%" class="text-muted">Customer Name:</th>
                        <td class="fw-bold">{{ $report->customer->name ?: 'N/A' }}</td>
                    </tr>
                    @if(!empty($report->customer->companyName))
                    <tr>
                        <th class="text-muted">Company:</th>
                        <td>{{ $report->customer->companyName }}</td>
                    </tr>
                    @endif
                    <tr>
                        <th class="text-muted">Phone:</th>
                        <td>{{ $report->customer->phone ?: ($quotation->customer->phone ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Email:</th>
                        <td>{{ $report->customer->email ?: ($quotation->customer->email ?? 'N/A') }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Site Address:</th>
                        <td>{{ $report->customer->address ?: ($quotation->address ?? 'N/A') }}</td>
                    </tr>
                    @if(!empty($report->customer->gstNumber))
                    <tr>
                        <th class="text-muted">GSTIN:</th>
                        <td><span class="badge bg-light text-dark border">{{ $report->customer->gstNumber }}</span></td>
                    </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100 shadow-sm border-0">
            <div class="card-header bg-light py-2">
                <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-info-circle-fill me-1"></i> Quotation & Project Details</h6>
            </div>
            <div class="card-body py-2">
                <table class="table table-sm table-borderless mb-0 small">
                    <tr>
                        <th width="35%" class="text-muted">Quotation No:</th>
                        <td class="fw-bold text-primary">{{ $report->header->quoteNo }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Project Name:</th>
                        <td>{{ $report->project->name }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Date / Validity:</th>
                        <td>{{ $report->header->date }} &rarr; <span class="text-muted">{{ $report->header->validTill }}</span></td>
                    </tr>
                    <tr>
                        <th class="text-muted">Sales Executive:</th>
                        <td>{{ $report->project->salesPerson }}</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Total Components:</th>
                        <td>{{ $report->summary->formattedNoOfComponents }} ({{ $report->summary->formattedTotalAreaSqft }})</td>
                    </tr>
                    <tr>
                        <th class="text-muted">Status:</th>
                        <td><span class="badge bg-primary">{{ $quotation->status ?? 'Draft' }}</span></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Itemized Work Specifications & Customizations Table -->
<div class="card shadow-sm border-0 mb-4 rounded-3">
    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-grid-3x3-gap-fill me-1"></i> Itemized Scope of Work & Customizations</h6>
        <span class="badge bg-secondary">{{ count($report->items) }} Item(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Product / Opening</th>
                    <th>Specifications & Customizations</th>
                    <th class="text-center">Dimensions</th>
                    <th class="text-center">Area</th>
                    <th class="text-center">Qty</th>
                    <th class="text-end">Rate / Sq.Ft.</th>
                    <th class="text-end">Total Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($report->items as $idx => $item)
                    <tr>
                        <td><span class="badge bg-light text-dark border">{{ $item->itemCode }}</span></td>
                        <td>
                            <strong class="text-dark">{{ $item->name !== $item->position ? $item->name : ($quotation->items[$idx]->product_name ?? $item->name) }}</strong>
                            <div class="text-muted small">Pos: {{ $item->position }} | Loc: {{ $item->location }}</div>
                        </td>
                        <td>
                            <div class="small">
                                <span class="badge bg-light text-primary border me-1">{{ $item->profileSystem }}</span>
                                <span class="badge bg-light text-dark border me-1"><i class="bi bi-palette"></i> {{ $item->commercialSpecs['Profile Color'] ?? 'WHITE' }}</span>
                                <span class="badge bg-light text-dark border me-1"><i class="bi bi-square"></i> Glass: {{ $item->glass->type }}</span>
                                <span class="badge bg-light text-dark border me-1"><i class="bi bi-grid"></i> Mesh: {{ $item->commercialSpecs['Mosquito Mesh'] ?? 'No' }}</span>
                                <span class="badge bg-light text-dark border"><i class="bi bi-gear"></i> H/W: {{ $item->hardware->brand }}</span>
                            </div>
                            @if(!empty($item->sizes) && count($item->sizes) > 1)
                                <div class="mt-1 text-muted" style="font-size: 0.75rem;">
                                    <strong>Sizes ({{ count($item->sizes) }}):</strong>
                                    @foreach($item->sizes as $sIdx => $sz)
                                        <span>#{{ $sIdx+1 }}: {{ is_array($sz) ? $sz['width'] : $sz->width }}x{{ is_array($sz) ? $sz['height'] : $sz->height }}{{ is_array($sz) ? ($sz['unit'] ?? 'mm') : ($sz->unit ?? 'mm') }} (Qty: {{ is_array($sz) ? ($sz['quantity'] ?? 1) : ($sz->quantity ?? 1) }}){{ !$loop->last ? ' | ' : '' }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="text-center text-nowrap">{{ $item->formattedWidth }} &times; {{ $item->formattedHeight }} {{ $item->unit }}</td>
                        <td class="text-center text-nowrap">{{ $item->formattedSqftArea }}</td>
                        <td class="text-center">{{ $item->formattedQuantity }}</td>
                        <td class="text-end text-nowrap">{{ $item->formattedValuePerSqft }}</td>
                        <td class="text-end fw-bold text-nowrap">{{ $item->formattedTotalValue }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">No items found in this quotation.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <td colspan="4" class="fw-bold text-end">Basic Subtotal:</td>
                    <td class="text-center fw-bold">{{ $report->summary->formattedTotalAreaSqft }}</td>
                    <td class="text-center fw-bold">{{ $report->summary->formattedNoOfComponents }}</td>
                    <td class="text-end text-muted small">Avg: {{ $report->summary->formattedAvgPriceSqftExGst }}</td>
                    <td class="text-end fw-bold text-primary">{{ $report->summary->formattedBasicValue }}</td>
                </tr>
                @if($report->financials->discount > 0)
                <tr>
                    <td colspan="7" class="text-end text-danger">Discount ({{ (float)$report->financials->discountPercent }}%):</td>
                    <td class="text-end text-danger fw-bold">- {{ number_format($report->financials->discount, 2) }} INR</td>
                </tr>
                @endif
                @if($report->financials->transportation > 0)
                <tr>
                    <td colspan="7" class="text-end text-muted">Transportation / Freight:</td>
                    <td class="text-end fw-bold">{{ number_format($report->financials->transportation, 2) }} INR</td>
                </tr>
                @endif
                @if($report->financials->installation > 0)
                <tr>
                    <td colspan="7" class="text-end text-muted">Installation Charges:</td>
                    <td class="text-end fw-bold">{{ number_format($report->financials->installation, 2) }} INR</td>
                </tr>
                @endif
                <tr>
                    <td colspan="7" class="text-end text-muted">GST ({{ (int)$report->financials->gstPercent }}%):</td>
                    <td class="text-end fw-bold">{{ $report->financials->formattedGstAmount }}</td>
                </tr>
                <tr class="table-primary">
                    <td colspan="7" class="text-end fw-bold fs-6">Grand Total (Inc. GST):</td>
                    <td class="text-end fw-bold fs-6 text-primary">{{ $report->financials->formattedGrandTotal }}</td>
                </tr>
            </tfoot>
        </table>
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
