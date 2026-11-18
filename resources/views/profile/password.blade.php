@extends('layouts.app')

@section('title', 'Change Password')
@section('page_title', 'Change Password')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-slate-800">Change Password</h2>
            <a href="{{ route('profile.show') }}" class="text-xs text-slate-500 hover:text-slate-700">
                <i data-lucide="arrow-left" class="w-3 h-3 mr-1"></i>
                Back
            </a>
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

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Current Password</label>
                <input type="password" name="current_password" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">New Password</label>
                <input type="password" name="password" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                <p class="text-[10px] text-slate-400 mt-1">At least 8 characters</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1 uppercase tracking-wider">Confirm New Password</label>
                <input type="password" name="password_confirmation" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <button type="submit" class="w-full py-3 px-4 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm shadow-teal-900/20 transition flex items-center justify-center gap-2">
                <i data-lucide="lock" class="w-4 h-4"></i>
                <span>Change Password</span>
            </button>
        </form>
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
