@extends('layouts.admin')
{{-- Phelo Mguca — 2330707726 | Group KN3 --}}
@section('title', 'Add Product')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.products.index') }}" class="items-center gap-1 py-2 text-sm text-stone-400 hover:text-[#333333] mb-4 inline-flex transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
        Back to Products
    </a>

    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
        @csrf

        @include('admin.products._form')

        <div class="flex gap-3">
            <button type="submit" class="btn-primary text-sm px-8 py-3">Create Product</button>
            <a href="{{ route('admin.products.index') }}" class="btn-secondary text-sm px-8 py-3">Cancel</a>
        </div>
    </form>
</div>
@endsection