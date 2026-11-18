@extends('layouts.app')

@section('title', 'Register')
@section('page_title', 'Create Account')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm">
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-xl bg-gradient-to-tr from-teal-500 to-teal-400 flex items-center justify-center text-white mx-auto mb-4 shadow-md shadow-teal-500/20">
                <i data-lucide="user-plus" class="w-8 h-8"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-800">Create Account</h2>
            <p class="text-slate-500 mt-2">Join RentMate and start tracking your rental expenses</p>
        </div>

        @if($errors->any())
        <div class="mb-4 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('register.store') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Full Name</label>
                <input type="text" name="name" required value="{{ old('name') }}" placeholder="e.g. Sajan Poudel" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Email Address</label>
                <input type="email" name="email" required value="{{ old('email') }}" placeholder="you@example.com" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Password</label>
                <input type="password" name="password" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                <p class="text-[10px] text-slate-400 mt-1">At least 8 characters</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Confirm Password</label>
                <input type="password" name="password_confirmation" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <button type="submit" class="w-full py-3 px-4 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm shadow-teal-900/20 transition flex items-center justify-center gap-2">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                <span>Create Account</span>
            </button>
        </form>

        <div class="mt-6 pt-6 border-t border-slate-100">
            <p class="text-center text-xs text-slate-500 mb-4">
                Already have an account?
                <a href="{{ route('login') }}" class="font-semibold text-teal-600 hover:text-teal-700">Sign in</a>
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
