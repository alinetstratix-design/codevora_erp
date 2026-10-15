@extends('layouts.app')
@section('title', $company->name)
@section('content')
@php
    $access = ['full' => 'Full access', 'read_only' => 'Read-only (grace period)', 'locked' => 'Locked'][$company->accessLevel()];
    $seatsUsed = $company->activeSeatCount();
    $seatLimit = $company->seatLimit();
    $seatPct = $seatLimit > 0 ? min(100, (int) round($seatsUsed / $seatLimit * 100)) : 0;
@endphp

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-4">
    <div>
        <a href="{{ route('platform.index') }}" class="small text-decoration-none"><i class="bi bi-arrow-left"></i> Dashboard</a>
        <h3 class="fw-bold text-dark mb-1 mt-1">{{ $company->name }}</h3>
        <span class="badge {{ $company->getSubscriptionBadge()['class'] }}">{{ $company->getSubscriptionBadge()['label'] }}</span>
        <span class="small text-muted ms-2">{{ $access }}</span>
    </div>
    <a href="{{ route('platform.companies.audit', $company) }}" class="btn btn-outline-secondary btn-sm">Subscription history</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Access ends</div><div class="fw-bold">{{ $company->expiryDate()?->format('d M Y') ?? 'No end date' }}</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Seats used</div><div class="fw-bold">{{ $seatsUsed }} / {{ $seatLimit }}</div><div class="progress mt-2" style="height:6px"><div class="progress-bar {{ $seatPct >= 100 ? 'bg-danger' : 'bg-primary' }}" style="width: {{ $seatPct }}%"></div></div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Quotations</div><div class="fw-bold">{{ $company->quotations_count }}</div><div class="small text-muted">Content is not shown here</div></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm"><div class="card-body"><div class="text-muted small">Customers</div><div class="fw-bold">{{ $company->customers_count }}</div><div class="small text-muted">Content is not shown here</div></div></div></div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Users</strong></div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Last sign-in</th><th class="text-end">Edit</th></tr>
                    </thead>
                    <tbody>
                    @forelse($users as $member)
                        <tr>
                            <td class="fw-semibold">{{ $member->name }}</td>
                            <td class="small">{{ $member->email }}</td>
                            <td colspan="4" class="p-0">
                                <form method="POST" action="{{ route('platform.companies.users.update', [$company, $member]) }}" class="row g-0 align-items-center py-2 pe-2">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-4 pe-1">
                                        <select name="role" class="form-select form-select-sm">
                                            @foreach($roles as $role)
                                                <option value="{{ $role }}" @selected($member->role === $role)>{{ $role }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-3 pe-1">
                                        <select name="status" class="form-select form-select-sm">
                                            <option value="Active" @selected($member->status === 'Active')>Active</option>
                                            <option value="Inactive" @selected($member->status !== 'Active')>Inactive</option>
                                        </select>
                                    </div>
                                    <div class="col-5 text-end text-nowrap">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                        <tr class="table-light">
                            <td colspan="6" class="py-1 small text-muted">
                                Last sign-in: {{ $member->last_login_at?->diffForHumans() ?? 'never' }}
                                <form method="POST" action="{{ route('platform.companies.admins.reset', [$company, $member]) }}" class="d-inline ms-2" onsubmit="return confirm('Generate a new temporary password for {{ $member->email }}?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link p-0 small">Reset password</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No users yet. Add the first one.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>Add user</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.companies.users.store', $company) }}">
                    @csrf
                    <div class="mb-2"><label class="form-label small">Name</label><input name="name" class="form-control form-control-sm" value="{{ old('name') }}" required></div>
                    <div class="mb-2"><label class="form-label small">Email</label><input type="email" name="email" class="form-control form-control-sm" value="{{ old('email') }}" required></div>
                    <div class="mb-3">
                        <label class="form-label small">Role</label>
                        <select name="role" class="form-select form-select-sm">
                            @foreach($roles as $role)
                                <option value="{{ $role }}" @selected(old('role') === $role)>{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm w-100" @disabled($seatsUsed >= $seatLimit)>Create user with temporary password</button>
                    @if($seatsUsed >= $seatLimit)
                        <div class="small text-danger mt-2">Seat limit reached. Increase it below first.</div>
                    @endif
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white"><strong>Seat limit</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.companies.seats', $company) }}" class="d-flex gap-2">
                    @csrf
                    @method('PATCH')
                    <input type="number" name="max_users" min="1" max="1000" value="{{ $seatLimit }}" class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                </form>
                <div class="small text-muted mt-2">Lowering the limit does not remove existing users. It only blocks new ones.</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><strong>Subscription</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('platform.companies.subscription', $company) }}" class="d-flex gap-2">
                    @csrf
                    <select name="action" class="form-select form-select-sm">
                        <option value="extend_3d">+3 days trial</option>
                        <option value="activate_paid">Activate / +30 days</option>
                        <option value="suspend">Suspend</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary">Apply</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
