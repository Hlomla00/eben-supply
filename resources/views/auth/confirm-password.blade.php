{{-- PRT362S — Eben Supply | Group KN3 --}}
@extends('layouts.app')
@section('title', 'Confirm Password')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-16 bg-[#F5F5F5]">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-black text-3xl text-[#333333] mb-2">Confirm Password</h1>
            <p class="text-stone-400 text-sm">This is a secure area. Please confirm your password to continue.</p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-100 shadow-card p-8">
            <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="form-input @error('password') ring-2 ring-red-300 @enderror"
                           placeholder="••••••••">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-sm">Confirm</button>
            </form>
        </div>
    </div>
</div>
@endsection
