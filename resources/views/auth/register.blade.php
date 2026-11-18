@extends('layouts.auth')

@section('title', 'Create Account - RentMate')

@section('content')

<!-- ============================================
     TOP HEADER BAR - Full width, logo left, back right
============================================ -->
<header class="bg-white/80 backdrop-blur-md border-b border-slate-200 shrink-0 w-full">
    <div class="px-6 py-4 flex items-center justify-between w-full max-w-7xl mx-auto">
        <!-- Logo - Left -->
        <a href="{{ route('landing') }}" class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white shadow-lg shadow-teal-500/30">
                <i data-lucide="home" class="w-5 h-5"></i>
            </div>
            <span class="text-lg font-bold tracking-tight text-slate-900">Rent<span class="text-teal-600">Mate</span></span>
        </a>

        <!-- Actions - Right -->
        <div class="flex items-center gap-2">
            <a href="{{ route('login') }}" class="flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/50 border border-slate-200 text-xs font-semibold text-slate-600 hover:text-teal-600 hover:bg-slate-100 transition-all">
                <i data-lucide="arrow-left" class="w-3 h-3"></i>
                <span class="hidden sm:inline">Back</span>
            </a>
        </div>
    </div>
</header>

<!-- ============================================
     MAIN CONTENT - Centered auth card
============================================ -->
<main class="flex-1 w-full max-w-md mx-auto py-12">
    <div class="w-full bg-white/90 backdrop-blur-md rounded-3xl border border-slate-200/80 p-8 sm:p-12 shadow-2xl relative overflow-hidden">
        <!-- Decorative gradient blobs -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl -mt-16 -mr-16"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-teal-500/5 rounded-full blur-3xl -mb-16 -ml-16"></div>

        <div class="relative z-10 text-center mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 mb-2">Create Account</h2>
            <p class="text-slate-500 text-sm">Join RentMate and start tracking your rental expenses</p>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm">
            <h4 class="text-xs font-semibold text-rose-900 mb-2 uppercase tracking-wider flex items-center gap-1.5">
                <i data-lucide="alert-triangle" class="w-3 h-3"></i>
                Please fix the following:
            </h4>
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Name -->
            <div class="text-left">
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">Full Name</label>
                <input type="text" name="name" required value="{{ old('name') }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:bg-white outline-none transition-all text-sm text-slate-900 placeholder-slate-400"
                    placeholder="e.g. Sajan Poudel">
            </div>

            <!-- Email -->
            <div class="text-left">
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:bg-white outline-none transition-all text-sm text-slate-900 placeholder-slate-400"
                    placeholder="you@example.com">
            </div>

            <!-- Password -->
            <div class="text-left">
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">Password</label>
                <div class="relative">
                    <input type="password" name="password" required id="register-password"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:bg-white outline-none transition-all text-sm text-slate-900 placeholder-slate-400 pr-10"
                        placeholder="••••••••">
                    <p class="text-[10px] text-slate-400 mt-1">At least 8 characters</p>
                    <button type="button" onclick="togglePassword('register-password')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-teal-600 transition">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="text-left">
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">Confirm Password</label>
                <div class="relative">
                    <input type="password" name="password_confirmation" required id="register-password-confirm"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:bg-white outline-none transition-all text-sm text-slate-900 placeholder-slate-400 pr-10"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword('register-password-confirm')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-teal-600 transition">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-teal-600 to-teal-700 text-white font-bold text-sm shadow-lg shadow-teal-900/20 hover:from-teal-700 hover:to-teal-800 hover:shadow-teal-500/40 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Create Account</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100/50">
            <p class="text-center text-xs text-slate-500 mb-6">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-teal-600 hover:text-teal-700 transition">Sign in</a>
            </p>
        </div>
    </div>
</main>

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
