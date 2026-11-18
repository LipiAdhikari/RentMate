@extends('layouts.auth')

@section('title', 'Sign In - RentMate')

@section('header')
<header class="bg-white/80 backdrop-blur-md border-b border-slate-200 shrink-0">
    <div class="max-w-md mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('landing') }}" class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-500 to-brand-600 flex items-center justify-center text-white shadow-sm shadow-brand-500/30">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
            <span class="text-lg font-bold tracking-tight text-slate-900">Rent<span class="text-brand-600">Mate</span></span>
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('register') }}" class="text-xs font-semibold text-slate-600 hover:text-brand-600 transition px-3 py-1.5 rounded-lg bg-white/50 border border-gray-200 hover:bg-slate-100">
                Create Account
            </a>
        </div>
    </div>
</header>
@endsection

@section('content')
<div class="w-full max-w-md">

    @component('components.auth-success')
    @endcomponent

    @component('components.auth-errors')
    @endcomponent

    <!-- Brand Header -->
    <div class="text-center mb-10">
        <div class="flex items-center justify-center mb-6">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
        </div>
        <h2 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">Welcome back</h2>
        <p class="text-lg text-slate-600">Sign in to manage your rent, payments, and monthly expenses.</p>
    </div>

    <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-6">
        @csrf

        @component('components.auth-input', [
            'name' => 'email',
            'label' => 'Email Address',
            'type' => 'email',
            'placeholder' => 'you@example.com',
            'icon' => 'mail',
            'autocomplete' => 'email',
            'error' => $errors->first('email')
        ])
        @endcomponent

        @component('components.auth-password', [
            'name' => 'password',
            'label' => 'Password',
            'placeholder' => '••••••••',
            'autocomplete' => 'current-password',
            'error' => $errors->first('password')
        ])
        @endcomponent

        <!-- Remember me -->
        <div class="flex items-center gap-3 mb-6">
            <input type="checkbox" name="remember" id="remember"
                class="w-4 h-4 rounded border-gray-200 text-brand-600 focus:ring-brand-500 focus:ring-offset-0 checked:bg-brand-600 checked:border-brand-600">
            <label for="remember" class="text-sm text-slate-600">Remember me</label>
        </div>

        <!-- Submit -->
        <x-auth-button text="Sign in" />

        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-center text-xs text-slate-500 mb-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-brand-600 hover:text-brand-700 transition">Create one</a>
            </p>

            <hr class="relative my-6">
            <span class="absolute -top-2.5 left-1/2 -translate-x-1/2 bg-white px-2 text-[10px] font-semibold text-slate-400 uppercase tracking-wider">
                Or continue with
            </span>

            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="w-full py-3 rounded-xl border border-gray-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-xs font-semibold text-slate-700 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="google" class="w-4 h-4 text-red-500"></i>
                    <span>Google</span>
                </button>
                <button type="button" class="w-full py-3 rounded-xl border border-gray-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-xs font-semibold text-slate-700 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="github" class="w-4 h-4 text-slate-600"></i>
                    <span>GitHub</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) window.lucide.createIcons();
    });

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        const button = input.parentElement.querySelector('button');
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.setAttribute('data-lucide', 'eye-off');
        } else {
            input.type = 'password';
            icon.setAttribute('data-lucide', 'eye');
        }
        if (window.lucide) window.lucide.createIcons();
    }
</script>
@endpush
