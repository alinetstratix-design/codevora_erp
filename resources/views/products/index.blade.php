@extends('layouts.app')

@section('title', 'Products')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-box-seam"></i> Products Library</h5>
        <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-circle"></i> Add Product</a>
    </div>
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="mb-3">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search by name, category, profile..." value="{{ request('search') }}">
                <button class="btn btn-outline-secondary" type="submit"><i class="bi bi-search"></i> Search</button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Image</th>
                        <th>Code</th>
                        <th>Name & Description</th>
                        <th>Category & Profile</th>
                        <th>Default Rate</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    <tr>
                        <td class="text-center">
                            @if($product->default_image)
                                <img src="{{ asset($product->default_image) }}" alt="Product" class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                            @else
                                <div class="bg-light d-flex align-items-center justify-content-center text-muted img-thumbnail" style="width: 50px; height: 50px;">
                                    <i class="bi bi-image"></i>
                                </div>
                            @endif
                        </td>
                        <td><span class="badge bg-secondary">{{ $product->product_code }}</span></td>
                        <td>
                            <strong>{{ $product->name }}</strong><br>
                            <small class="text-muted">{{ Str::limit($product->description, 30) }}</small>
                        </td>
                        <td>
                            <div><span class="badge bg-info text-dark">{{ $product->category ?? 'N/A' }}</span></div>
                            <small class="text-muted">{{ $product->profile ?? 'No Profile' }}</small>
                        </td>
                        <td>₹{{ number_format($product->base_rate, 2) }}</td>
                        <td>
                            <span class="badge {{ $product->status == 'Active' ? 'bg-success' : 'bg-danger' }}">
                                {{ $product->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-3">No products found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-end mt-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
