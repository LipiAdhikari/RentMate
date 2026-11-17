@extends('layouts.app')

@section('title', 'Rental Settings')
@section('page_title', 'Configuration & Rental Settings')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Rate Preservation Guarantee Notice -->
    <div class="bg-teal-50 border border-teal-200 rounded-2xl p-5 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-teal-600/10 text-teal-700 flex items-center justify-center shrink-0">
                <i data-lucide="shield" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-teal-950 text-sm">Contractual Rate Preservation</h4>
                <p class="text-xs text-teal-800/80 mt-1 leading-relaxed">
                    Changing these settings updates your default values for future monthly records. 
                    <strong>Historical records preserve the exact contractual rates that applied when they were logged</strong>, guaranteeing financial audit integrity.
                </p>
            </div>
        </div>
    </div>

    <form action="{{ route('settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Financial Parameters -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="banknote" class="w-4 h-4 text-teal-600"></i>
                <span>Default Financial Parameters</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Monthly House Rent (NPR) *</label>
                    <input type="number" step="0.01" min="0" name="rent_amount" required value="{{ old('rent_amount', $settings->rent_amount) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    <span class="text-[10px] text-slate-400">Default: NPR 16,000.00</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Electricity Rate per Unit (NPR) *</label>
                    <input type="number" step="0.01" min="0" name="electricity_rate" required value="{{ old('electricity_rate', $settings->electricity_rate) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    <span class="text-[10px] text-slate-400">Default: NPR 10.00 / unit</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Water Bill Share Percentage (%) *</label>
                    <input type="number" step="0.01" min="0" max="100" name="water_share_percentage" required value="{{ old('water_share_percentage', $settings->water_share_percentage) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    <span class="text-[10px] text-slate-400">Default: 50.00%</span>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Currency Code *</label>
                    <input type="text" name="currency" required value="{{ old('currency', $settings->currency) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tenant Move-in Date</label>
                    <input type="date" name="rental_start_date" value="{{ old('rental_start_date', $settings->rental_start_date ? $settings->rental_start_date->format('Y-m-d') : '') }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>
            </div>
        </div>

        <!-- Landlord & Property Information -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="home" class="w-4 h-4 text-slate-600"></i>
                <span>Property & Landlord Details</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Landlord / Owner Name</label>
                    <input type="text" name="owner_name" value="{{ old('owner_name', $settings->owner_name) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Landlord Phone Number</label>
                    <input type="text" name="owner_phone" value="{{ old('owner_phone', $settings->owner_phone) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Landlord Email</label>
                    <input type="email" name="owner_email" value="{{ old('owner_email', $settings->owner_email) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Reminder Due Day</label>
                    <input type="number" min="1" max="31" name="payment_reminder_days" required value="{{ old('payment_reminder_days', $settings->payment_reminder_days) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    <span class="text-[10px] text-slate-400">Day of the month rent is due (e.g. 5th)</span>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Property Address</label>
                <input type="text" name="property_address" value="{{ old('property_address', $settings->property_address) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lease / Agreement Notes</label>
                <textarea name="notes" rows="2" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 p-3 border">{{ old('notes', $settings->notes) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3">
            <button type="submit" class="px-5 py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition flex items-center gap-2">
                <i data-lucide="save" class="w-4 h-4"></i>
                <span>Save Rental Settings</span>
            </button>
        </div>
    </form>

</div>
@endsection
