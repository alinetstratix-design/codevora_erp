<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $product->name ?? '') }}" required placeholder="e.g. 2 Track Sliding Window">
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Category</label>
        <select name="category" class="form-select @error('category') is-invalid @enderror">
            <option value="">Select Category</option>
            @php $categories = ['Sliding Window', 'Casement Window', 'Fixed Window', 'Sliding Door', 'Casement Door', 'Other']; @endphp
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ old('category', $product->category ?? '') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3">
        <label class="form-label fw-bold">Profile Brand</label>
        <input type="text" name="profile_brand" class="form-control @error('profile_brand') is-invalid @enderror" value="{{ old('profile_brand', $product->profile_brand ?? 'CORA') }}" placeholder="e.g. CORA">
        @error('profile_brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Profile Series</label>
        <input type="text" name="profile_series" class="form-control @error('profile_series') is-invalid @enderror" value="{{ old('profile_series', $product->profile_series ?? '60MM CASEMENT SERIES') }}" placeholder="e.g. 60MM CASEMENT SERIES">
        @error('profile_series')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Opening Type</label>
        <input type="text" name="opening_type" class="form-control @error('opening_type') is-invalid @enderror" value="{{ old('opening_type', $product->opening_type ?? 'Casement Outward') }}" placeholder="e.g. Casement Outward">
        @error('opening_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Mesh Type</label>
        <input type="text" name="mesh_type" class="form-control @error('mesh_type') is-invalid @enderror" value="{{ old('mesh_type', $product->mesh_type ?? 'No') }}" placeholder="e.g. SS FLYMESH / No">
        @error('mesh_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label fw-bold">Glass Type</label>
        <input type="text" name="glass_type" class="form-control @error('glass_type') is-invalid @enderror" value="{{ old('glass_type', $product->glass_type ?? '(1) 5mm Clear Toughened') }}" placeholder="e.g. (1) 5mm Clear Toughened">
        @error('glass_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Glass Thickness</label>
        <input type="text" name="glass_thickness" class="form-control @error('glass_thickness') is-invalid @enderror" value="{{ old('glass_thickness', $product->glass_thickness ?? '5mm') }}" placeholder="e.g. 5mm">
        @error('glass_thickness')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Hardware Brand</label>
        <input type="text" name="hardware_brand" class="form-control @error('hardware_brand') is-invalid @enderror" value="{{ old('hardware_brand', $product->hardware_brand ?? 'CORA Hardware') }}" placeholder="e.g. CORA Hardware">
        @error('hardware_brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label fw-bold">Base Rate per Sq.Ft. (₹)</label>
        <input type="number" step="0.01" name="base_rate" class="form-control @error('base_rate') is-invalid @enderror" value="{{ old('base_rate', $product->base_rate ?? '600') }}" required>
        @error('base_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Unit / UOM</label>
        <input type="text" name="unit" class="form-control @error('unit') is-invalid @enderror" value="{{ old('unit', $product->unit ?? 'mm') }}" placeholder="mm / cm / inch">
        @error('unit')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="Active" {{ old('status', $product->status ?? 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ old('status', $product->status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Product Image</label>
        <input type="file" name="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        @if(isset($product) && $product->default_image)
            <div class="mt-2">
                <img src="{{ asset($product->default_image) }}" alt="Current Image" class="img-thumbnail" style="max-height: 100px;">
            </div>
        @endif
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Description / Technical Notes</label>
        <textarea name="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $product->description ?? '') }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<!-- Advanced BOM & Pricing (EvA Engine) -->
<h5 class="mt-4 border-bottom pb-2 text-success"><i class="bi bi-calculator"></i> Advanced BOM & Dynamic Pricing</h5>
<p class="small text-muted mb-4">Set these values to enable dynamic price calculation based on actual material usage during Quotation.</p>

<div class="row mb-3">
    <div class="col-md-3">
        <label class="form-label fw-bold">Profile Wt. (Kg/m)</label>
        <input type="number" step="0.001" name="profile_weight_per_mtr" class="form-control" value="{{ old('profile_weight_per_mtr', $product->profile_weight_per_mtr ?? '0') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Profile Rate (₹/Kg)</label>
        <input type="number" step="0.01" name="profile_rate_per_kg" class="form-control" value="{{ old('profile_rate_per_kg', $product->profile_rate_per_kg ?? '0') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Glass Rate (₹/Sq.Ft)</label>
        <input type="number" step="0.01" name="glass_rate_per_sqft" class="form-control" value="{{ old('glass_rate_per_sqft', $product->glass_rate_per_sqft ?? '0') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Hardware Kit (₹/Item)</label>
        <input type="number" step="0.01" name="hardware_kit_cost" class="form-control" value="{{ old('hardware_kit_cost', $product->hardware_kit_cost ?? '0') }}">
    </div>
</div>
<div class="row mb-4">
    <div class="col-md-3">
        <label class="form-label fw-bold">Wastage (%)</label>
        <input type="number" step="0.01" name="wastage_percent" class="form-control" value="{{ old('wastage_percent', $product->wastage_percent ?? '10') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Profit Margin (%)</label>
        <input type="number" step="0.01" name="profit_margin_percent" class="form-control" value="{{ old('profit_margin_percent', $product->profit_margin_percent ?? '20') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Labor (₹/Sq.Ft)</label>
        <input type="number" step="0.01" name="labor_rate_per_sqft" class="form-control" value="{{ old('labor_rate_per_sqft', $product->labor_rate_per_sqft ?? '0') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold">Calculation Formula</label>
        <select name="profile_calc_formula" class="form-select">
            <option value="PERIMETER" {{ old('profile_calc_formula', $product->profile_calc_formula ?? '') == 'PERIMETER' ? 'selected' : '' }}>Standard Perimeter (2W+2H)</option>
            <option value="FIXED_MULTIPLIER" {{ old('profile_calc_formula', $product->profile_calc_formula ?? '') == 'FIXED_MULTIPLIER' ? 'selected' : '' }}>Old Fixed Multiplier</option>
        </select>
    </div>
</div>

<!-- Exhaustive Detail Templates (Linked to 0% Tolerance PDF Engine) -->
<h5 class="mt-4 border-bottom pb-2 text-primary"><i class="bi bi-sliders"></i> Default Automation Specs (Optional)</h5>
<p class="small text-muted mb-4">Filling these out here will auto-populate the Detailed Specs modal when a sales executive selects this product in a Quotation. Blank fields will naturally be ignored by the PDF engine.</p>

<div class="row mb-4">
    <!-- Profile Details Defaults -->
    <div class="col-md-6 border-end">
        <h6 class="text-secondary mb-3"><i class="bi bi-bounding-box-circles"></i> Profile Detail Defaults</h6>
        <div class="row g-2">
            @php 
                $p = $product->profile_details ?? []; 
                $a = $product->accessories_details ?? [];
            @endphp
            <div class="col-6"><label class="small fw-bold">Profile Color</label><input type="text" name="profile_details[Profile Color]" class="form-control form-control-sm" value="{{ old('profile_details.Profile Color', $p['Profile Color'] ?? 'WHITE') }}"></div>
            <div class="col-6"><label class="small fw-bold">MeshType</label><input type="text" name="profile_details[MeshType]" class="form-control form-control-sm" value="{{ old('profile_details.MeshType', $p['MeshType'] ?? 'No') }}"></div>
            <div class="col-12"><label class="small fw-bold">Casement Sash</label><input type="text" name="profile_details[Casement Sash]" class="form-control form-control-sm" value="{{ old('profile_details.Casement Sash', $p['Casement Sash'] ?? '') }}"></div>
            <div class="col-12"><label class="small fw-bold">Casement Sash Ri</label><input type="text" name="profile_details[Casement Sash Ri]" class="form-control form-control-sm" value="{{ old('profile_details.Casement Sash Ri', $p['Casement Sash Ri'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Outer</label><input type="text" name="profile_details[Outer]" class="form-control form-control-sm" value="{{ old('profile_details.Outer', $p['Outer'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Outer Ri</label><input type="text" name="profile_details[Outer Ri]" class="form-control form-control-sm" value="{{ old('profile_details.Outer Ri', $p['Outer Ri'] ?? '') }}"></div>
            <div class="col-12"><label class="small fw-bold">Door Panel</label><input type="text" name="profile_details[Door Panel]" class="form-control form-control-sm" value="{{ old('profile_details.Door Panel', $p['Door Panel'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Sash Mullion</label><input type="text" name="profile_details[Sash Mullion]" class="form-control form-control-sm" value="{{ old('profile_details.Sash Mullion', $p['Sash Mullion'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Mullion Ri</label><input type="text" name="profile_details[Mullion Ri]" class="form-control form-control-sm" value="{{ old('profile_details.Mullion Ri', $p['Mullion Ri'] ?? '') }}"></div>
            <div class="col-12"><label class="small fw-bold">Coupler</label><input type="text" name="profile_details[Coupler]" class="form-control form-control-sm" value="{{ old('profile_details.Coupler', $p['Coupler'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Flymesh Sash</label><input type="text" name="profile_details[Flymesh Sash]" class="form-control form-control-sm" value="{{ old('profile_details.Flymesh Sash', $p['Flymesh Sash'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Flymesh Sash Ri</label><input type="text" name="profile_details[Flymesh Sash Ri]" class="form-control form-control-sm" value="{{ old('profile_details.Flymesh Sash Ri', $p['Flymesh Sash Ri'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Guide Rail</label><input type="text" name="profile_details[Guide Rail]" class="form-control form-control-sm" value="{{ old('profile_details.Guide Rail', $p['Guide Rail'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Interlock</label><input type="text" name="profile_details[Interlock]" class="form-control form-control-sm" value="{{ old('profile_details.Interlock', $p['Interlock'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Sliding Sash</label><input type="text" name="profile_details[Sliding Sash]" class="form-control form-control-sm" value="{{ old('profile_details.Sliding Sash', $p['Sliding Sash'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Sliding Sash Ri</label><input type="text" name="profile_details[Sliding Sash Ri]" class="form-control form-control-sm" value="{{ old('profile_details.Sliding Sash Ri', $p['Sliding Sash Ri'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Track</label><input type="text" name="profile_details[Track]" class="form-control form-control-sm" value="{{ old('profile_details.Track', $p['Track'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Track Ri</label><input type="text" name="profile_details[Track Ri]" class="form-control form-control-sm" value="{{ old('profile_details.Track Ri', $p['Track Ri'] ?? '') }}"></div>
        </div>
    </div>
    
    <!-- Accessories Details Defaults -->
    <div class="col-md-6">
        <h6 class="text-secondary mb-3"><i class="bi bi-tools"></i> Accessory Detail Defaults</h6>
        <div class="row g-2">
            <div class="col-6"><label class="small fw-bold">Locking</label><input type="text" name="accessories_details[Locking]" class="form-control form-control-sm" value="{{ old('accessories_details.Locking', $a['Locking'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Handle color</label><input type="text" name="accessories_details[Handle color]" class="form-control form-control-sm" value="{{ old('accessories_details.Handle color', $a['Handle color'] ?? 'WHITE') }}"></div>
            <div class="col-6"><label class="small fw-bold">Arm Restrictor</label><input type="text" name="accessories_details[Arm Restrictor]" class="form-control form-control-sm" value="{{ old('accessories_details.Arm Restrictor', $a['Arm Restrictor'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Cylinder</label><input type="text" name="accessories_details[Cylinder]" class="form-control form-control-sm" value="{{ old('accessories_details.Cylinder', $a['Cylinder'] ?? '') }}"></div>
            <div class="col-12"><label class="small fw-bold">Handle Type 1</label><input type="text" name="accessories_details[Handle Type 1]" class="form-control form-control-sm" value="{{ old('accessories_details.Handle Type 1', $a['Handle Type 1'] ?? '') }}"></div>
            <div class="col-12"><label class="small fw-bold">Handle Type 2</label><input type="text" name="accessories_details[Handle Type 2]" class="form-control form-control-sm" value="{{ old('accessories_details.Handle Type 2', $a['Handle Type 2'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Hinge 1</label><input type="text" name="accessories_details[Hinge 1]" class="form-control form-control-sm" value="{{ old('accessories_details.Hinge 1', $a['Hinge 1'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Hinge 2</label><input type="text" name="accessories_details[Hinge 2]" class="form-control form-control-sm" value="{{ old('accessories_details.Hinge 2', $a['Hinge 2'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Friction</label><input type="text" name="accessories_details[Friction]" class="form-control form-control-sm" value="{{ old('accessories_details.Friction', $a['Friction'] ?? '') }}"></div>
            <div class="col-6"><label class="small fw-bold">Roller</label><input type="text" name="accessories_details[Roller]" class="form-control form-control-sm" value="{{ old('accessories_details.Roller', $a['Roller'] ?? '') }}"></div>
            <div class="col-12"><label class="small fw-bold">Flymesh Handle Type</label><input type="text" name="accessories_details[Flymesh Handle Type]" class="form-control form-control-sm" value="{{ old('accessories_details.Flymesh Handle Type', $a['Flymesh Handle Type'] ?? '') }}"></div>
        </div>
    </div>
</div>
