@extends('layouts.app')

@section('title', 'Fabrication BOM Breakdown - ' . ($quotation->quotation_number ?? $quotation->quote_no))

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="text-primary mb-0"><i class="bi bi-list-check"></i> Production-Grade Fabrication BOM — {{ $quotation->quotation_number ?? $quotation->quote_no }}</h4>
    <div>
        <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to Quote</a>
        <a href="{{ route('quotations.downloadPdf', $quotation->id) }}" class="btn btn-danger"><i class="bi bi-file-pdf"></i> Download EvA PDF</a>
    </div>
</div>

@foreach($boms as $index => $bom)
<div class="card shadow-sm mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">Item {{ $index + 1 }}: {{ $bom['item_code'] }} ({{ $bom['dimensions'] }}) — Quantity: {{ $bom['total_qty'] }} Pcs</h6>
        <span class="badge bg-light text-dark fs-6">Area: {{ $bom['total_area_sqft'] }} Sq.Ft | Weight: {{ $bom['total_weight_kg'] }} KG</span>
    </div>
    <div class="card-body">
        
        <!-- Fabrication Cost Summary Matrix -->
        <div class="row g-2 mb-4 text-center">
            <div class="col-md-2">
                <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block fw-bold">Profiles Cost</small>
                    <span class="fw-bold text-primary">₹{{ number_format($bom['profile_cost'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block fw-bold">Glass Cost</small>
                    <span class="fw-bold text-success">₹{{ number_format($bom['glass_cost'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block fw-bold">Steel Reinforcement</small>
                    <span class="fw-bold text-dark">₹{{ number_format($bom['steel_cost'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block fw-bold">Hardware Cost</small>
                    <span class="fw-bold text-info">₹{{ number_format($bom['hardware_cost'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2 border rounded bg-light">
                    <small class="text-muted d-block fw-bold">Accessories & Gaskets</small>
                    <span class="fw-bold text-secondary">₹{{ number_format($bom['accessories_cost'], 2) }}</span>
                </div>
            </div>
            <div class="col-md-2">
                <div class="p-2 border rounded bg-primary text-white">
                    <small class="d-block fw-bold opacity-75">Total BOM Cost</small>
                    <span class="fw-bold fs-6">₹{{ number_format($bom['total_bom_cost'], 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Detailed Material Line Items Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle mb-0 text-sm" style="font-size: 8.5pt;">
                <thead class="table-dark">
                    <tr>
                        <th>Category</th>
                        <th>Material Name</th>
                        <th>SKU</th>
                        <th>Supplier</th>
                        <th>UOM</th>
                        <th>Length (mm)</th>
                        <th>Qty</th>
                        <th>Weight (KG)</th>
                        <th>Waste %</th>
                        <th>Rate (₹)</th>
                        <th>Total Cost (₹)</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bom['line_items'] as $line)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $line['material_category'] }}</span></td>
                        <td class="fw-bold">{{ $line['material_name'] }}</td>
                        <td><code>{{ $line['sku'] }}</code></td>
                        <td>{{ $line['supplier'] }}</td>
                        <td class="text-center">{{ $line['unit'] }}</td>
                        <td class="text-end">{{ $line['length'] > 0 ? number_format($line['length'], 1) : '-' }}</td>
                        <td class="text-end fw-bold">{{ number_format($line['quantity'], 2) }}</td>
                        <td class="text-end">{{ $line['weight_kg'] > 0 ? number_format($line['weight_kg'], 2) : '-' }}</td>
                        <td class="text-center text-muted">{{ $line['waste_percent'] }}%</td>
                        <td class="text-end">₹{{ number_format($line['rate'], 2) }}</td>
                        <td class="text-end fw-bold text-success">₹{{ number_format($line['total_cost'], 2) }}</td>
                        <td class="small text-muted">{{ $line['remarks'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endforeach
@endsection
