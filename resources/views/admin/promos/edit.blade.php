@extends('layouts.admin')
{{-- PRT362S — Eben Supply | Group KN3 --}}
@section('title', 'Edit Promo Code')

@section('content')
<div class="max-w-2xl">
    <a href="{{ route('admin.promos.index') }}" class="flex items-center gap-1 text-sm text-stone-400 hover:text-[#333333] mb-4 py-2 inline-flex transition-colors font-medium">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7-7-7 7 7 7"/></svg>
        Back to Promo Codes
    </a>

    <form action="{{ route('admin.promos.update', $promo) }}" method="POST" class="space-y-5">
        @csrf @method('PUT')

        @include('admin.promos._form')

        <div class="flex gap-3">
            <button type="submit" class="btn-primary text-sm px-8 py-3">Update Code</button>
            <a href="{{ route('admin.promos.index') }}" class="btn-secondary text-sm px-8 py-3">Cancel</a>
        </div>
    </form>
</div>
@endsection
