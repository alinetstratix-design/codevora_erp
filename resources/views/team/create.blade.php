@extends('layouts.app')
@section('title', 'Add user')
@section('content')
<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('team.store') }}">
            @csrf
            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', 'Staff') === $role)>{{ $role }}</option>
                    @endforeach
                </select>
                <div class="form-text">Admin: full access and team management. Manager: products, master data, approvals. Staff: quotations and customers.</div>
            </div>
            <p class="small text-muted">A temporary password is generated. The user must change it at first sign-in.</p>
            <button type="submit" class="btn btn-primary">Create user</button>
            <a href="{{ route('team.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>
@endsection
