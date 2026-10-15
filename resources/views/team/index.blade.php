@extends('layouts.app')
@section('title', 'Team')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Users who can sign in to {{ auth()->user()->company?->name }}.</p>
    <a href="{{ route('team.create') }}" class="btn btn-primary"><i class="bi bi-person-plus me-1"></i> Add user</a>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Last sign-in</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $member)
                <tr>
                    <td>{{ $member->name }} @if($member->id === auth()->id())<span class="badge bg-secondary">You</span>@endif</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->role }}</td>
                    <td>
                        <span class="badge {{ $member->status === 'Active' ? 'bg-success' : 'bg-secondary' }}">{{ $member->status }}</span>
                        @if($member->must_change_password)<span class="badge bg-warning text-dark">Temp password</span>@endif
                    </td>
                    <td class="small text-muted">{{ $member->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="text-end">
                        <a href="{{ route('team.edit', $member) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="POST" action="{{ route('team.reset-password', $member) }}" class="d-inline" onsubmit="return confirm('Generate a new temporary password for this user?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Reset password</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
