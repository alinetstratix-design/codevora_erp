@extends('layouts.app')
@section('title', 'Activity')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">Permanent record of changes in {{ auth()->user()->company?->name }}. Entries cannot be edited or removed.</p>
    <form method="GET" class="d-flex gap-2">
        <select name="action" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All activity</option>
            @foreach($actions as $action)
                <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
            @endforeach
        </select>
    </form>
</div>
<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>When</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Record</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td class="small text-nowrap">{{ $log->created_at?->timezone(config('app.timezone'))->format('d M Y, H:i') }}</td>
                    <td>{{ $log->user_name ?? 'System' }} @if($log->user_role)<span class="badge bg-secondary">{{ $log->user_role }}</span>@endif</td>
                    <td><span class="badge {{ $log->action === 'quotation.deleted' ? 'bg-danger' : 'bg-primary' }}">{{ $log->action }}</span></td>
                    <td>{{ $log->subject_label ?? '-' }}</td>
                    <td class="small text-muted">
                        @foreach(($log->details ?? []) as $key => $value)
                            @if($value !== null && $value !== '')<span class="me-2">{{ $key }}: <strong>{{ is_scalar($value) ? $value : json_encode($value) }}</strong></span>@endif
                        @endforeach
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted py-4">No activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-3">{{ $logs->links() }}</div>
@endsection
