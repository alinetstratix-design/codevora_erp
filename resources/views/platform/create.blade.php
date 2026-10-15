@extends('layouts.app')
@section('title', 'New company')
@section('content')
<div class="card border-0 shadow-sm" style="max-width: 680px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('platform.companies.store') }}">
            @csrf
            <h2 class="h6 text-uppercase text-muted">Company</h2>
            <div class="row g-3 mb-4">
                <div class="col-12">
                    <label for="company_name" class="form-label">Company name</label>
                    <input type="text" id="company_name" name="company_name" class="form-control" value="{{ old('company_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="company_email" class="form-label">Company email</label>
                    <input type="email" id="company_email" name="company_email" class="form-control" value="{{ old('company_email') }}">
                </div>
                <div class="col-md-6">
                    <label for="company_phone" class="form-label">Phone</label>
                    <input type="text" id="company_phone" name="company_phone" class="form-control" value="{{ old('company_phone') }}">
                </div>
                <div class="col-md-6">
                    <label for="company_gstin" class="form-label">GSTIN (optional now, can be added later)</label>
                    <input type="text" id="company_gstin" name="company_gstin" class="form-control" value="{{ old('company_gstin') }}">
                </div>
            </div>

            <h2 class="h6 text-uppercase text-muted">First admin account</h2>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label for="admin_name" class="form-label">Admin name</label>
                    <input type="text" id="admin_name" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label for="admin_email" class="form-label">Admin email (login)</label>
                    <input type="email" id="admin_email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" required>
                </div>
            </div>
            <p class="small text-muted">A temporary password is shown once after saving. The admin sets their own password at first sign-in and then adds their team.</p>
            <button type="submit" class="btn btn-primary">Create company</button>
            <a href="{{ route('platform.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>
@endsection
