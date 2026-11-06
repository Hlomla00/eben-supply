@extends('errors.layout')
{{-- PRT362S — Eben Supply | Group KN3 --}}
@section('code', '419')
@section('title', 'Page expired')
@section('message', 'This page was open for too long, so the form expired for your security. Go back, refresh the page and try again.')
@section('actions')
    <a href="{{ url()->previous() }}" class="btn-primary text-sm justify-center">Go back</a>
    <a href="{{ url('/') }}" class="btn-secondary text-sm justify-center">Back to the store</a>
@endsection
