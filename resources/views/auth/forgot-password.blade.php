{{-- PRT362S — Eben Supply | Group KN3 --}}
@extends('layouts.app')
@section('title', 'Forgot Password')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-16 bg-[#F5F5F5]">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-black text-3xl text-[#333333] mb-2">Forgot Password</h1>
            <p class="text-stone-400 text-sm">Enter your email and we'll send you a link to choose a new password.</p>
        </div>

        @if (session('status'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm rounded-lg mb-4">{{ session('status') }}</div>
        @endif

        <div class="bg-white rounded-2xl border border-stone-100 shadow-card p-8">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="form-input @error('email') ring-2 ring-red-300 @enderror"
                           placeholder="you@email.com">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-sm">Email Password Reset Link</button>

                <p class="text-center text-sm text-stone-400">
                    Remembered it?
                    <a href="{{ route('login') }}" class="text-[#333333] hover:text-[#A3A380] transition-colors font-semibold">Sign In</a>
                </p>
            </form>
        </div>
    </div>
</div>
@endsection
