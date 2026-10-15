@extends('layouts.app')
@section('title', 'Change password')
@section('content')
<div class="card border-0 shadow-sm" style="max-width: 520px;">
    <div class="card-body p-4">
        @if(auth()->user()->must_change_password)
            <div class="alert alert-info small">You are using a temporary password. Choose a new one to continue.</div>
        @endif
        <form method="POST" action="{{ route('account.password.update') }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="current_password" class="form-label">Current password</label>
                <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">New password</label>
                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="8" required>
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm new password</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" required>
            </div>
            <button type="submit" class="btn btn-primary">Save password</button>
        </form>
    </div>
</div>
@endsection
