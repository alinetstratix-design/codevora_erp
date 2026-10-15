@extends('layouts.app')
@section('title', 'Companies')
@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Companies</div><div class="fs-3 fw-bold">{{ $stats['companies'] }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Active companies</div><div class="fs-3 fw-bold">{{ $stats['active_companies'] }}</div></div></div></div>
    <div class="col-md-4"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Company users</div><div class="fs-3 fw-bold">{{ $stats['company_users'] }}</div></div></div></div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Each company gets its own data, settings and admin account.</p>
    <a href="{{ route('platform.companies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New company</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Company</th>
                    <th>Status</th>
                    <th>Users</th>
                    <th>Admin accounts</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td class="fw-semibold">{{ $company->name }}</td>
                    <td><span class="badge {{ $company->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $company->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>{{ $company->users_count }}</td>
                    <td>
                        @forelse($company->users as $admin)
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="small">{{ $admin->email }}</span>
                                <form method="POST" action="{{ route('platform.companies.admins.reset', [$company, $admin]) }}" onsubmit="return confirm('Generate a new temporary password for {{ $admin->email }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0">Reset password</button>
                                </form>
                            </div>
                        @empty
                            <span class="text-muted small">No admin yet</span>
                        @endforelse
                    </td>
                    <td class="text-end">
                        <form method="POST" action="{{ route('platform.companies.toggle', $company) }}" class="d-inline">
                            @csrf
                            @method('PATCH')
                            @if($company->is_active)
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Deactivate this company? All its users will be signed out.');">Deactivate</button>
                            @else
                                <button type="submit" class="btn btn-sm btn-outline-success">Activate</button>
                            @endif
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No companies yet. Create the first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
