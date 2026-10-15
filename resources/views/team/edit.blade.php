@extends('layouts.app')
@section('title', 'Edit user')
@section('content')
<div class="card border-0 shadow-sm" style="max-width: 560px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('team.update', $member) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" value="{{ $member->email }}" disabled>
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Full name</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
            </div>
            <div class="mb-3">
                <label for="role" class="form-label">Role</label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected(old('role', $member->role) === $role)>{{ $role }}</option>
                    @endforeach
                </select>
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="Active" @selected(old('status', $member->status) === 'Active')>Active</option>
                    <option value="Inactive" @selected(old('status', $member->status) === 'Inactive')>Inactive (cannot sign in)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save changes</button>
            <a href="{{ route('team.index') }}" class="btn btn-link">Cancel</a>
        </form>
    </div>
</div>
@endsection
