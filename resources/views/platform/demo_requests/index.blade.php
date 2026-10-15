@extends('layouts.app')
@section('title', 'Demo & Trial Requests')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-dark mb-1">Demo &amp; Trial Requests</h3>
        <p class="text-muted small mb-0">Review potential client requests and provision 3-day full-access trial workspaces with automated credentials.</p>
    </div>
    <div>
        <a href="{{ route('platform.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Companies
        </a>
    </div>
</div>

<!-- Filter Tabs -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-2 d-flex gap-2">
        <a href="{{ route('platform.demo-requests.index', ['status' => 'all']) }}" class="btn btn-sm {{ $status === 'all' ? 'btn-primary' : 'btn-light' }}">
            All Requests
        </a>
        <a href="{{ route('platform.demo-requests.index', ['status' => 'pending']) }}" class="btn btn-sm {{ $status === 'pending' ? 'btn-primary' : 'btn-light' }}">
            Pending Review
            @if($pendingCount > 0)
                <span class="badge bg-danger ms-1">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('platform.demo-requests.index', ['status' => 'approved']) }}" class="btn btn-sm {{ $status === 'approved' ? 'btn-primary' : 'btn-light' }}">
            Approved Trials
        </a>
        <a href="{{ route('platform.demo-requests.index', ['status' => 'rejected']) }}" class="btn btn-sm {{ $status === 'rejected' ? 'btn-primary' : 'btn-light' }}">
            Rejected
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Applicant Details</th>
                    <th>Company / Business</th>
                    <th>Fabrication Type</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                <tr>
                    <td>
                        <div class="fw-bold text-dark">{{ $req->name }}</div>
                        <div class="small text-muted"><i class="bi bi-envelope me-1"></i> {{ $req->email }}</div>
                        <div class="small text-muted"><i class="bi bi-telephone me-1"></i> {{ $req->phone }}</div>
                        @if($req->location)
                            <div class="small text-muted"><i class="bi bi-geo-alt me-1"></i> {{ $req->location }}</div>
                        @endif
                    </td>
                    <td>
                        <div class="fw-semibold">{{ $req->company_name }}</div>
                        @if($req->company)
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 mt-1 small">
                                Provisioned ID: #{{ $req->company->id }}
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border">
                            {{ $req->fabrication_type ?: 'General' }}
                        </span>
                    </td>
                    <td class="small text-muted">
                        <div>{{ $req->created_at->format('d M Y, h:i A') }}</div>
                        <div class="text-secondary opacity-75">{{ $req->created_at->diffForHumans() }}</div>
                    </td>
                    <td>
                        @if($req->status === 'pending')
                            <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i> Pending</span>
                        @elseif($req->status === 'approved')
                            <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Approved</span>
                        @else
                            <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Rejected</span>
                        @endif
                    </td>
                    <td class="text-end">
                        @if($req->status === 'pending')
                            <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#approveModal-{{ $req->id }}">
                                <i class="bi bi-check-lg me-1"></i> Approve (3-Day Trial)
                            </button>

                            <form method="POST" action="{{ route('platform.demo-requests.reject', $req) }}" class="d-inline" onsubmit="return confirm('Reject this request?');">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>

                            <!-- Approve Modal -->
                            <div class="modal fade text-start" id="approveModal-{{ $req->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="{{ route('platform.demo-requests.approve', $req) }}">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold">Provision 3-Day Trial</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="mb-2">You are about to approve access for:</p>
                                                <div class="bg-light p-3 rounded-3 mb-3 border">
                                                    <div><strong>Name:</strong> {{ $req->name }}</div>
                                                    <div><strong>Company:</strong> {{ $req->company_name }}</div>
                                                    <div><strong>Email:</strong> {{ $req->email }}</div>
                                                    <div><strong>Duration:</strong> <span class="badge bg-warning text-dark">3 Days Full Access</span></div>
                                                </div>
                                                <p class="small text-muted mb-0">
                                                    This will automatically create the <strong>Company workspace</strong>, create the <strong>Company Admin account</strong>, and dispatch their live login credentials via email.
                                                </p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success btn-sm">Confirm &amp; Dispatch Credentials</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @else
                            <small class="text-muted fst-italic">Processed</small>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-2 d-block mb-2 text-secondary opacity-50"></i>
                        No requests found in this category.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($requests->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $requests->links() }}
        </div>
    @endif
</div>
@endsection
