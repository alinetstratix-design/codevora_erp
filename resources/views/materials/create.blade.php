@extends('layouts.app')

@section('title', 'Add Material')

@section('content')
<div class="container-fluid pb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-dark mb-0">Add New Material</h3>
        <a href="{{ route('materials.index') }}" class="btn btn-light border shadow-sm rounded-pill px-4 hover-lift">
            <i class="bi bi-arrow-left me-2"></i> Back to List
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form action="{{ route('materials.store') }}" method="POST">
                @csrf
                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">SKU Code <span class="text-danger">*</span></label>
                        <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" required placeholder="e.g. FRAME-60MM">
                        @error('sku') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Material Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required placeholder="e.g. 60mm Casement Outer Frame">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Category <span class="text-danger">*</span></label>
                        <select name="category" class="form-select @error('category') is-invalid @enderror" required>
                            <option value="">-- Select Category --</option>
                            <option value="Profile" @if(old('category') == 'Profile') selected @endif>Profile (Aluminum/UPVC)</option>
                            <option value="Hardware" @if(old('category') == 'Hardware') selected @endif>Hardware</option>
                            <option value="Glass" @if(old('category') == 'Glass') selected @endif>Glass</option>
                            <option value="Steel" @if(old('category') == 'Steel') selected @endif>Steel Reinforcement</option>
                            <option value="Accessory" @if(old('category') == 'Accessory') selected @endif>Accessory / Miscellaneous</option>
                        </select>
                        @error('category') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Unit of Measure (UOM) <span class="text-danger">*</span></label>
                        <select name="uom" class="form-select @error('uom') is-invalid @enderror" required>
                            <option value="">-- Select Unit --</option>
                            <option value="mtr" @if(old('uom') == 'mtr') selected @endif>Meter (mtr)</option>
                            <option value="sqft" @if(old('uom') == 'sqft') selected @endif>Square Feet (sqft)</option>
                            <option value="sqm" @if(old('uom') == 'sqm') selected @endif>Square Meter (sqm)</option>
                            <option value="nos" @if(old('uom') == 'nos') selected @endif>Numbers / Pieces (nos)</option>
                            <option value="kg" @if(old('uom') == 'kg') selected @endif>Kilogram (kg)</option>
                        </select>
                        @error('uom') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Base Cost (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="cost" class="form-control @error('cost') is-invalid @enderror" value="{{ old('cost') }}" required placeholder="0.00">
                        @error('cost') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Wastage / Buffer (%)</label>
                        <input type="number" step="0.01" name="waste_percent" class="form-control @error('waste_percent') is-invalid @enderror" value="{{ old('waste_percent', '0') }}">
                        <div class="form-text">Added dynamically to BOM calculations.</div>
                        @error('waste_percent') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top text-end">
                    <button type="submit" class="btn btn-primary rounded-pill shadow-sm px-5 fw-bold hover-lift">
                        Save Material <i class="bi bi-check-lg ms-2"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
