{{-- PRT362S — Eben Supply | Group KN3 --}}
@extends('layouts.app')
@section('title', 'Verify Email')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center px-4 py-16 bg-[#F5F5F5]">
    <div class="w-full max-w-md">

        <div class="text-center mb-8">
            <h1 class="font-heading font-black text-3xl text-[#333333] mb-2">Verify Your Email</h1>
            <p class="text-stone-400 text-sm">Thanks for signing up! Please click the link we emailed you. Didn't get it? We'll send another.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm rounded-lg mb-4">
                A new verification link has been sent to the email address you registered with.
            </div>
        @endif

        <div class="bg-white rounded-2xl border border-stone-100 shadow-card p-8 space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="btn-primary w-full py-3 text-sm">Resend Verification Email</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-center text-sm text-stone-400 hover:text-[#333333] transition-colors font-medium">Sign Out</button>
            </form>
        </div>
    </div>
</div>
@endsection
