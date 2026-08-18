@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-person-plus"></i> Add Customer</h5>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Back</a>
    </div>
    <div class="card-body">
        <form action="{{ route('customers.store') }}" method="POST">
            @csrf
            
            @include('customers._form')

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle"></i> Save Customer</button>
            </div>
        </form>
    </div>
</div>
@endsection
