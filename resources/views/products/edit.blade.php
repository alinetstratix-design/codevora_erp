@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-pencil-square"></i> Edit Product: {{ $product->product_code }}</h5>
        <a href="{{ route('products.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            @include('products._form')

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle"></i> Update Product</button>
            </div>
        </form>
    </div>
</div>
@endsection
