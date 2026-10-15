@extends('layouts.app')
@section('title', 'Subscription audit: ' . $company->name)
@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="mb-3">Subscription history for {{ $company->name }}</h5>
        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr><th>When</th><th>Action</th><th>By</th><th>Before</th><th>After</th><th>Note</th></tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                        <td><code>{{ $log->action }}</code></td>
                        <td>{{ $log->actor?->name ?? 'System' }}</td>
                        <td class="small text-muted">{{ collect($log->before)->map(fn($v, $k) => "$k: " . ($v ?? '-'))->implode(' | ') }}</td>
                        <td class="small">{{ collect($log->after)->map(fn($v, $k) => "$k: " . ($v ?? '-'))->implode(' | ') }}</td>
                        <td class="small">{{ $log->note }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No subscription changes recorded yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $logs->links() }}
        <a href="{{ route('platform.index') }}" class="btn btn-sm btn-outline-secondary mt-2">Back to companies</a>
    </div>
</div>
@endsection
