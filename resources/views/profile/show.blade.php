@extends('layouts.app')

@section('title', 'My Profile')
@section('page_title', 'Profile Settings')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Profile Header -->
    <div class="bg-white rounded-2xl p-8 border border-slate-200/80 shadow-sm text-center">
        <div class="w-20 h-20 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center text-4xl mx-auto mb-4 shadow-sm shadow-teal-500/20">
            <i data-lucide="user" class="w-10 h-10"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800">{{ $user->name }}</h2>
        <p class="text-slate-500 text-sm">{{ $user->email }}</p>
        <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-[10px] font-bold border bg-emerald-50 text-emerald-700 border-emerald-200">
            <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
            Member since {{ $user->created_at->format('M d, Y') }}
        </span>
    </div>

    <!-- Account Information Card -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-teal-600"></i>
                <span>Account Information</span>
            </h3>
            <a href="{{ route('profile.edit') }}" class="text-xs text-teal-600 hover:text-teal-700 font-semibold">
                Edit Profile
            </a>
        </div>

        <div class="space-y-4">
            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Name</p>
                    <p class="font-medium text-slate-800">{{ $user->name }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Email</p>
                    <p class="font-medium text-slate-800">{{ $user->email }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-xl border border-slate-100">
                <div>
                    <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider">Account Type</p>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border bg-teal-50 text-teal-700 border-teal-200">
                        Personal Tenant
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Rental Settings Summary -->
    @if($user->rentalSetting)
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <h3 class="font-bold text-slate-800 text-sm mb-6 flex items-center gap-2">
            <i data-lucide="sliders" class="w-4 h-4 text-teal-600"></i>
            <span>Rental Settings Summary</span>
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Monthly Rent</p>
                <p class="font-bold text-slate-800">NPR {{ number_format($user->rentalSetting->rent_amount, 0) }}</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Electricity Rate</p>
                <p class="font-bold text-slate-800">NPR {{ number_format($user->rentalSetting->electricity_rate, 0) }}/unit</p>
            </div>
            <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                <p class="text-[10px] text-slate-400 uppercase font-semibold tracking-wider block">Water Share</p>
                <p class="font-bold text-slate-800">{{ $user->rentalSetting->water_share_percentage }}%</p>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-1.5 text-xs text-teal-600 hover:text-teal-700 font-semibold">
                <i data-lucide="edit-3" class="w-3 h-3"></i>
                <span>Manage Settings</span>
            </a>
        </div>
    </div>
    @endif

    <!-- Change Password -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="lock" class="w-4 h-4 text-amber-500"></i>
                <span>Change Password</span>
            </h3>
            <a href="{{ route('profile.password') }}" class="text-xs text-teal-600 hover:text-teal-700 font-semibold">
                Change Password
            </a>
        </div>

        <p class="text-xs text-slate-500 mb-6">
            Keep your account secure by updating your password regularly.
        </p>

        <div class="flex items-center justify-between p-3 bg-amber-50 rounded-xl border border-amber-100">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-amber-800">Password security</p>
                    <p class="text-[10px] text-amber-600">Use a strong, unique password for your RentMate account.</p>
                </div>
            </div>
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
