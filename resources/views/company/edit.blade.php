@extends('layouts.app')

@section('title', 'ERP Company & System Settings')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="bi bi-building"></i> Company Profile & ERP Settings</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('company.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name', $setting->company_name ?? auth()->user()?->company?->name ?? '') }}" placeholder="Enter legal company name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">GSTIN / GST Number</label>
                            <input type="text" name="gst_number" class="form-control" value="{{ old('gst_number', $setting->gst_number ?? $setting->gstin ?? '') }}" placeholder="e.g. 09AAAAA0000A1Z5">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Phone Number</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $setting->phone ?? '') }}" placeholder="+91 XXXXXXXXXX">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $setting->email ?? auth()->user()?->company?->email ?? '') }}" placeholder="contact@company.com">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Website URL</label>
                            <input type="text" name="website" class="form-control" value="{{ old('website', $setting->website ?? '') }}" placeholder="https://example.com">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Registered Office Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Registered office address">{{ old('address', $setting->address ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Default Bank Details</label>
                            <textarea name="bank_details" class="form-control" rows="4" placeholder="50% Advance with Order&#10;50% Before Delivery">{{ old('bank_details', is_array($setting->bank_details) ? implode("\n", $setting->bank_details) : $setting->bank_details) }}</textarea>
                            <small class="text-muted">Enter each payment term on a new line.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Default Terms & Conditions</label>
                            <textarea name="terms_conditions" class="form-control" rows="4" placeholder="1. Payment terms...&#10;2. Validation 30 days...">{{ old('terms_conditions', is_array($setting->terms_conditions) ? implode("\n", $setting->terms_conditions) : $setting->terms_conditions) }}</textarea>
                            <small class="text-muted">Enter each term condition on a new line.</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Pre-Requisites for Installation of Windows</label>
                            <textarea name="installation_prerequisites" class="form-control" rows="4" placeholder="1. Walls plastered inside and outside...&#10;2. Jams, sills plastered...">{{ old('installation_prerequisites', is_array($setting->installation_prerequisites) ? implode("\n", $setting->installation_prerequisites) : $setting->installation_prerequisites) }}</textarea>
                            <small class="text-muted">Enter each installation prerequisite clause on a new line.</small>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Company Logo Image</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                            @if(!empty($setting->logo))
                                <div class="mt-2">
                                    <img src="{{ asset($setting->logo) }}" alt="Logo" class="img-thumbnail" style="max-height: 90px;">
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Authorized Signature Stamp Image</label>
                            <input type="file" name="authorized_signature" class="form-control" accept="image/*">
                            @if(!empty($setting->authorized_signature))
                                <div class="mt-2">
                                    <img src="{{ asset($setting->authorized_signature) }}" alt="Signature" class="img-thumbnail" style="max-height: 90px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-lg px-5 shadow-sm"><i class="bi bi-check-circle"></i> Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
