@extends('layouts.auth')

@section('title', 'Sign In - RentMate')

@section('content')

<!-- ============================================
     TOP HEADER BAR - Full width, logo left, action right
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
            <a href="{{ route('register') }}" class="px-4 py-2.5 rounded-xl bg-white/50 border border-slate-200 text-xs font-semibold text-slate-600 hover:text-teal-600 hover:bg-slate-100 transition-all flex items-center gap-2">
                <i data-lucide="user-plus" class="w-3 h-3"></i>
                <span class="hidden sm:inline">Create Account</span>
            </a>
        </div>
    </div>
</header>

<!-- ============================================
     MAIN CONTENT - Centered auth card
============================================ -->
<main class="flex-1 w-full max-w-md mx-auto py-8 px-4 flex flex-col justify-center">
    <div class="w-full bg-white/90 backdrop-blur-md rounded-3xl border border-slate-200/80 p-8 sm:p-12 shadow-2xl relative overflow-hidden">
        <!-- Decorative gradient blobs -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-teal-500/10 rounded-full blur-3xl -mt-16 -mr-16"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-teal-500/5 rounded-full blur-3xl -mb-16 -ml-16"></div>

        <div class="relative z-10 text-center mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-slate-900 mb-2">Welcome back</h2>
            <p class="text-slate-500 text-sm">Sign in to manage your rental expenses</p>
        </div>

        <!-- Alerts -->
        @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-4 h-4 mt-0.5 shrink-0 text-rose-600"></i>
            <span>{{ session('error') }}</span>
        </div>
        @endif

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

        <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-5">
            @csrf

            <!-- Email -->
            <div class="text-left">
                <label class="block text-xs font-semibold text-slate-600 mb-2 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" required value="{{ old('email') }}"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:bg-white outline-none transition-all text-sm text-slate-900 placeholder-slate-400"
                    placeholder="you@example.com">
            </div>

            <!-- Password -->
            <div class="text-left">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Password</label>
                    <a href="#" class="text-xs text-teal-600 hover:text-teal-700 font-medium">Forgot password?</a>
                </div>
                <div class="relative">
                    <input type="password" name="password" required id="login-password"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50/50 focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20 focus:bg-white outline-none transition-all text-sm text-slate-900 placeholder-slate-400 pr-10"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword('login-password')"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-teal-600 transition">
                        <i data-lucide="eye" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- Remember me -->
            <div class="flex items-center gap-3">
                <input type="checkbox" name="remember" id="remember"
                    class="w-4 h-4 rounded border-slate-200 text-teal-600 focus:ring-teal-500 focus:ring-offset-0 checked:bg-teal-600 checked:border-teal-600">
                <label for="remember" class="text-sm text-slate-600">Remember me</label>
            </div>

            <!-- Submit -->
            <button type="submit"
                class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-teal-600 to-teal-700 text-white font-bold text-sm shadow-lg shadow-teal-900/20 hover:from-teal-700 hover:to-teal-800 hover:shadow-teal-500/40 transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Sign in</span>
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-100/50">
            <p class="text-center text-xs text-slate-500 mb-6">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-teal-600 hover:text-teal-700 transition">Create one</a>
            </p>

            <div class="relative my-6">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-slate-200"></div>
                </div>
                <div class="relative flex justify-center text-[10px] uppercase">
                    <span class="bg-white px-2 font-semibold text-slate-400 tracking-wider">Or continue with</span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <button type="button" class="w-full py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-xs font-medium text-slate-700 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="google" class="w-4 h-4 text-red-500"></i>
                    <span>Google</span>
                </button>
                <button type="button" class="w-full py-3 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 hover:border-slate-300 text-xs font-medium text-slate-700 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="github" class="w-4 h-4 text-slate-600"></i>
                    <span>GitHub</span>
                </button>
            </div>
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
