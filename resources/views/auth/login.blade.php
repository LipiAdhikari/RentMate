@extends('layouts.app')

@section('title', 'Login')
@section('page_title', 'Sign In')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white mx-auto mb-4 shadow-md shadow-teal-500/20">
                <i data-lucide="home" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Welcome back</h2>
            <p class="text-slate-500 mt-2">Sign in to your RentMate account</p>
        </div>

        @if(session('error'))
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            {{ session('error') }}
        </div>
        @endif

        @if($errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('login.authenticate') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" required value="{{ old('email') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Password</label>
                <input type="password" name="password" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-slate-300 rounded">
                    <label for="remember" class="ml-2 text-xs text-slate-600">Remember me</label>
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-4 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm shadow-teal-900/20 transition flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                <span>Sign In</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100">
            <p class="text-center text-xs text-slate-500 mb-4">
                Don't have an account?
                <a href="{{ route('register') }}" class="font-semibold text-teal-600 hover:text-teal-700">Create one</a>
            </p>
        </div>
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
