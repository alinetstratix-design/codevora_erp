@extends('layouts.guest')
@section('title', 'Forgot password')
@section('content')
    <h1 class="h5 mb-2">Forgot your password?</h1>
    <p class="text-muted small mb-3">Enter your work email. If an account exists, we will send a reset link.</p>
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary w-100">Send reset link</button>
    </form>
@endsection
