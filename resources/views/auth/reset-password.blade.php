@extends('layouts.guest')
@section('title', 'Reset password')
@section('content')
    <h1 class="h5 mb-3">Choose a new password</h1>
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $email) }}" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">New password</label>
            <input type="password" id="password" name="password" class="form-control" minlength="8" required>
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm new password</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" minlength="8" required>
        </div>
        <button type="submit" class="btn btn-primary w-100">Reset password</button>
    </form>
@endsection
