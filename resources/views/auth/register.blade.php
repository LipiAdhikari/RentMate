@extends('layouts.auth')

@section('title', 'Create Account - RentMate')

@section('header')
<header class="bg-white/80 backdrop-blur-md border-b border-slate-200 shrink-0">
    <div class="max-w-md mx-auto px-6 py-4 flex items-center justify-between">
        <a href="{{ route('login') }}" class="flex items-center gap-2 px-3 py-2 rounded-lg text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-all">
            <i data-lucide="arrow-left" class="w-4 h-4"></i>
            <span class="text-xs font-medium hidden sm:inline">Back</span>
        </a>
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
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-brand-500 to-brand-600 flex items-center justify-center text-white shadow-lg shadow-brand-500/30">
                <i data-lucide="user-plus" class="w-5 h-5"></i>
            </div>
        </div>
        <h2 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">Create your account</h2>
        <p class="text-lg text-slate-600">Start managing your rental expenses with clarity and confidence.</p>
    </div>

    <form action="{{ route('register.store') }}" method="POST" class="space-y-6">
        @csrf

        @component('components.auth-input', [
            'name' => 'name',
            'label' => 'Full Name',
            'placeholder' => 'Sajan Poudel',
            'icon' => 'user',
            'autocomplete' => 'name',
            'error' => $errors->first('name')
        ])
        @endcomponent

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
            'autocomplete' => 'new-password',
            'error' => $errors->first('password')
        ])
        @endcomponent

        @component('components.auth-password', [
            'name' => 'password_confirmation',
            'label' => 'Confirm Password',
            'placeholder' => '••••••••',
            'autocomplete' => 'new-password',
            'error' => $errors->first('password_confirmation')
        ])
        @endcomponent

        <x-auth-button text="Create account" />

        <div class="mt-8 pt-6 border-t border-gray-200">
            <p class="text-center text-xs text-slate-500 mb-6">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-brand-600 hover:text-brand-700 transition">Sign in</a>
            </p>
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
