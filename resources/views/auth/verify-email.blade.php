@extends('layouts.auth')

@section('title', 'Verify Email - RentMate')

@section('header')
<header class="bg-white/80 backdrop-blur-md border-b border-slate-200 shrink-0">
    <div class="max-w-md mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-500 to-brand-600 flex items-center justify-center text-white shadow-sm shadow-brand-500/30">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
            <span class="text-lg font-bold tracking-tight text-slate-900">Rent<span class="text-brand-600">Mate</span></span>
        </a>
    </div>
</header>
@endsection

@section('content')
<div class="w-full max-w-md">

    @component('components.auth-success')
    @endcomponent

    @component('components.auth-errors')
    @endcomponent

    <div class="text-center mb-10">
        <div class="flex items-center justify-center mb-6">
            <div class="w-10 h-10 rounded-xl bg-brand-100 text-brand-600 flex items-center justify-center">
                <i data-lucide="mail" class="w-5 h-5"></i>
            </div>
        </div>
        <h2 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">Verify your email</h2>
        <p class="text-lg text-slate-600">We have sent a verification link to your email address. Please verify your account to continue.</p>
    </div>

    <div class="space-y-5">
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-3">
                <i data-lucide="check-circle" class="w-4 h-4 mt-0.5 shrink-0 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <div class="text-center">
            <p class="text-sm text-slate-600 mb-6">Didn't receive the email?</p>
            <form action="{{ route('verification.resend') }}" method="POST" class="flex items-center justify-center">
                @csrf
                <x-auth-button text="Resend Verification Email" class="w-auto px-6" />
            </form>
        </div>
    </div>

    <div class="mt-8 pt-6 border-t border-gray-200">
        <p class="text-center text-xs text-slate-500 mb-6">
            Already verified?
            <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700 transition">Sign in</a>
        </p>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) window.lucide.createIcons();
    });
</script>
@endpush
