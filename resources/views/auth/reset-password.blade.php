{{-- PRT362S — Eben Supply | Group KN3 --}}
@extends('layouts.app')
@section('title', 'Reset Password')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-16 bg-[#F5F5F5]">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-black text-3xl text-[#333333] mb-2">Reset Password</h1>
            <p class="text-stone-400 text-sm">Choose a new password for your Eben Supply account.</p>
        </div>

        <div class="bg-white rounded-2xl border border-stone-100 shadow-card p-8">
            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username"
                           class="form-input @error('email') ring-2 ring-red-300 @enderror">
                    @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="password">New Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                           class="form-input @error('password') ring-2 ring-red-300 @enderror"
                           placeholder="••••••••">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                           class="form-input @error('password_confirmation') ring-2 ring-red-300 @enderror"
                           placeholder="••••••••">
                    @error('password_confirmation')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <button type="submit" class="btn-primary w-full py-3 text-sm">Reset Password</button>
            </form>
        </div>
    </div>
</div>
@endsection
