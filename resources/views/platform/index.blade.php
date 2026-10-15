@extends('layouts.app')
@section('title', 'Super Admin Dashboard')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Super Admin Dashboard</h3>
        <p class="text-muted small mb-0">Every company, its subscription and users, plus the trial approval queue.</p>
    </div>
    <a href="{{ route('platform.companies.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> New company</a>
</div>

<div class="row g-3 mb-4">
    @php
        $cards = [
            ['Companies', $stats['companies'], 'bi-buildings', 'text-primary'],
            ['Active companies', $stats['active_companies'], 'bi-check-circle', 'text-success'],
            ['On free trial', $stats['on_trial'], 'bi-hourglass-split', 'text-warning'],
            ['On paid plan', $stats['on_paid'], 'bi-award', 'text-success'],
            ['Expiring in 7 days', $stats['expiring_soon'], 'bi-alarm', 'text-danger'],
            ['Access ended', $stats['ended'], 'bi-lock', 'text-secondary'],
            ['Users (active / total)', $stats['active_users'].' / '.$stats['company_users'], 'bi-people', 'text-info'],
            ['Pending trial requests', $stats['pending_requests'], 'bi-inbox', 'text-danger'],
        ];
    @endphp
    @foreach($cards as [$label, $value, $icon, $tone])
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small"><i class="bi {{ $icon }} {{ $tone }} me-1"></i>{{ $label }}</div>
                    <div class="fs-3 fw-bold">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Approval queue: trial requests</strong>
                <a href="{{ route('platform.demo-requests.index', ['status' => 'pending']) }}" class="small">View all</a>
            </div>
            <div class="list-group list-group-flush">
                @forelse($pendingRequests as $req)
                    <div class="list-group-item d-flex justify-content-between align-items-center gap-2 flex-wrap">
                        <div>
                            <div class="fw-semibold">{{ $req->company_name }}</div>
                            <div class="small text-muted">{{ $req->name }} &middot; {{ $req->email }} &middot; {{ $req->created_at->diffForHumans() }}</div>
                        </div>
                        <div class="d-flex gap-1">
                            <form method="POST" action="{{ route('platform.demo-requests.approve', $req) }}" onsubmit="return confirm('Approve and create the trial workspace for {{ $req->company_name }}?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form method="POST" action="{{ route('platform.demo-requests.reject', $req) }}" onsubmit="return confirm('Reject this request?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">Reject</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="list-group-item text-center text-muted py-4">No pending requests.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white"><strong>Expiring within 7 days</strong></div>
            <div class="list-group list-group-flush">
                @forelse($expiringSoon as $company)
                    <a href="{{ route('platform.companies.show', $company) }}" class="list-group-item list-group-item-action d-flex justify-content-between">
                        <span>{{ $company->name }}</span>
                        <span class="small text-muted">{{ $company->expiryDate()?->format('d M Y') }}</span>
                    </a>
                @empty
                    <div class="list-group-item text-center text-muted py-4">Nobody expires this week.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white"><strong>Companies</strong></div>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Company</th>
                    <th>Subscription</th>
                    <th>Users</th>
                    <th>Admin</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($companies as $company)
                <tr>
                    <td class="fw-semibold">
                        <a href="{{ route('platform.companies.show', $company) }}" class="text-decoration-none">{{ $company->name }}</a>
                        @unless($company->is_active)<span class="badge bg-secondary ms-1">Deactivated</span>@endunless
                    </td>
                    <td><span class="badge {{ $company->getSubscriptionBadge()['class'] }}">{{ $company->getSubscriptionBadge()['label'] }}</span></td>
                    <td>{{ $company->users_count }} / {{ $company->seatLimit() }}</td>
                    <td class="small">{{ $company->users->first()?->email ?? 'No admin yet' }}</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('platform.companies.show', $company) }}" class="btn btn-sm btn-primary">Manage</a>
                        <a href="{{ route('platform.companies.audit', $company) }}" class="btn btn-sm btn-outline-secondary">History</a>
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
