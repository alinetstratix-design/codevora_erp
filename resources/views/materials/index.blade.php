@extends('layouts.app')

@section('title', 'Master Data - Materials')

@section('content')
<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Materials & Profiles</h3>
            <p class="text-muted small">Manage master data for hardware, glass, and profile costing.</p>
        </div>
        <a href="{{ route('materials.create') }}" class="btn btn-primary rounded-pill shadow-sm px-4">
            <i class="bi bi-plus-lg me-2"></i> Add Material
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">SKU</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>UOM</th>
                            <th>Base Cost (₹)</th>
                            <th>Wastage (%)</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($materials as $material)
                        <tr>
                            <td class="ps-4 fw-semibold text-primary">{{ $material->sku }}</td>
                            <td class="fw-bold text-dark">{{ $material->name }}</td>
                            <td>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-2 py-1 rounded-pill">
                                    {{ $material->category }}
                                </span>
                            </td>
                            <td class="text-muted">{{ strtoupper($material->uom) }}</td>
                            <td class="fw-semibold">₹{{ number_format($material->cost, 2) }}</td>
                            <td>{{ $material->waste_percent }}%</td>
                            <td class="text-end pe-4">
                                <a href="{{ route('materials.edit', $material->id) }}" class="btn btn-sm btn-light rounded-pill border shadow-sm px-3 hover-lift">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">No materials found. Add one to get started.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($materials->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $materials->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
