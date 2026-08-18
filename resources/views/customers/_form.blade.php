<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Customer Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $customer->name ?? '') }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Company Name</label>
        <input type="text" name="company_name" class="form-control @error('company_name') is-invalid @enderror" value="{{ old('company_name', $customer->company_name ?? '') }}">
        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">Mobile</label>
        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $customer->phone ?? '') }}">
        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $customer->email ?? '') }}">
        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-6">
        <label class="form-label fw-bold">GST Number</label>
        <input type="text" name="gst_number" class="form-control @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $customer->gst_number ?? '') }}">
        @error('gst_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-6">
        <label class="form-label fw-bold">Status</label>
        <select name="status" class="form-select @error('status') is-invalid @enderror">
            <option value="Active" {{ old('status', $customer->status ?? 'Active') == 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ old('status', $customer->status ?? '') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-12">
        <label class="form-label fw-bold">Address</label>
        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $customer->address ?? '') }}</textarea>
        @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4">
        <label class="form-label fw-bold">City</label>
        <input type="text" name="city" class="form-control @error('city') is-invalid @enderror" value="{{ old('city', $customer->city ?? '') }}">
        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">State</label>
        <input type="text" name="state" class="form-control @error('state') is-invalid @enderror" value="{{ old('state', $customer->state ?? '') }}">
        @error('state')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-md-4">
        <label class="form-label fw-bold">Pincode</label>
        <input type="text" name="pincode" class="form-control @error('pincode') is-invalid @enderror" value="{{ old('pincode', $customer->pincode ?? '') }}">
        @error('pincode')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <label class="form-label fw-bold">Notes</label>
        <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="3">{{ old('notes', $customer->notes ?? '') }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
