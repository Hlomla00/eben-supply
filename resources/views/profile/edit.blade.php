@extends('layouts.app')
{{-- PRT362S — Eben Supply | Group KN3 --}}
@section('title', 'Profile')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

    <div class="mb-8">
        <p class="section-label">Your Account</p>
        <h1 class="section-title">Profile</h1>
    </div>

    <div class="space-y-6">
        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="card p-6 sm:p-8">
            @include('profile.partials.update-password-form')
        </div>

        <div class="card p-6 sm:p-8">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
</div>
@endsection
