@extends('layouts.app')

@section('title', 'Customers')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> Customers</h5>
        <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Customer</a>
    </div>
    <div class="card-body">
        <form action="{{ route('customers.index') }}" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name, company, email, phone..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Phone & Email</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr>
                        <td><span class="badge bg-secondary">{{ $customer->customer_code }}</span></td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->company_name ?? '-' }}</td>
                        <td>
                            <div><i class="bi bi-telephone text-muted"></i> {{ $customer->phone ?? '-' }}</div>
                            <div><i class="bi bi-envelope text-muted"></i> {{ $customer->email ?? '-' }}</div>
                        </td>
                        <td>
                            <span class="badge {{ $customer->status == 'Active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $customer->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('customers.edit', $customer->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('customers.destroy', $customer->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this customer?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-3">No customers found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
