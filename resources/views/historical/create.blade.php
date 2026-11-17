@extends('layouts.app')

@section('title', 'Add Historical Record')
@section('page_title', 'Enter Historical Rental Record')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Add Historical Record</h2>
            <p class="text-xs text-slate-500">Log older rental records from your past 2 years of living here.</p>
        </div>
        <a href="{{ route('historical.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
            Back to Historical
        </a>
    </div>

    <form action="{{ route('historical.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Verification status toggle card -->
        <div class="bg-indigo-50/70 border border-indigo-200/80 rounded-2xl p-5 shadow-sm space-y-3">
            <h3 class="font-bold text-indigo-950 text-sm flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-indigo-600"></i>
                <span>Data Confidence & Verification</span>
            </h3>
            <p class="text-xs text-indigo-800/80">
                Mark whether this historical record is backed by receipts/bank transactions, or is an estimated record.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                <label class="flex items-start gap-3 p-3.5 rounded-xl border bg-white cursor-pointer hover:border-teal-500 transition">
                    <input type="radio" name="is_verified" value="1" checked class="mt-0.5 text-teal-600 focus:ring-teal-500">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">Verified Record</span>
                        <span class="text-[11px] text-slate-400">I have receipts, bank statement entries, or exact figures.</span>
                    </div>
                </label>
                <label class="flex items-start gap-3 p-3.5 rounded-xl border bg-white cursor-pointer hover:border-amber-500 transition">
                    <input type="radio" name="is_verified" value="0" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">Estimated / Incomplete</span>
                        <span class="text-[11px] text-slate-400">Figures are based on memory or some utility bills are missing.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Month & Rent -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="calendar" class="w-4 h-4 text-teal-600"></i>
                <span>1. Historical Period & Rent</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rental Year *</label>
                    <select name="year" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                        @for($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ old('year', $defaultYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rental Month *</label>
                    <select name="month" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                        @foreach([
                            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
                        ] as $num => $name)
                            <option value="{{ $num }}" {{ old('month', $defaultMonth) == $num ? 'selected' : '' }}>
                                {{ $name }} ({{ sprintf('%02d', $num) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rent Amount (NPR) *</label>
                    <input type="number" step="0.01" min="0" name="rent_amount" required value="{{ old('rent_amount', $settings->rent_amount) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>
            </div>
        </div>

        <!-- Electricity & Water (Optional / Estimated) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 pb-3 border-b border-slate-100">
                <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                <span>2. Utilities (Leave 0 or blank if receipts are lost)</span>
            </h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Previous Meter Reading</label>
                    <input type="number" step="0.01" min="0" name="prev_electricity_reading" value="{{ old('prev_electricity_reading', 0) }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Current Meter Reading</label>
                    <input type="number" step="0.01" min="0" name="curr_electricity_reading" value="{{ old('curr_electricity_reading', 0) }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rate (NPR/unit)</label>
                    <input type="number" step="0.01" min="0" name="electricity_rate" required value="{{ old('electricity_rate', $settings->electricity_rate) }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Total Water Bill from Authority (NPR)</label>
                    <input type="number" step="0.01" min="0" name="total_water_bill" value="{{ old('total_water_bill', 0) }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tenant Share Percentage (%)</label>
                    <input type="number" step="0.01" min="0" max="100" name="tenant_water_percentage" required value="{{ old('tenant_water_percentage', $settings->water_share_percentage) }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
                </div>
            </div>
        </div>

        <!-- Optional Payment Remembered -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                    <span>3. Known Payment (Optional)</span>
                </h3>
                <span class="text-xs text-rose-500 font-semibold">Do not guess missing amounts!</span>
            </div>

            <p class="text-xs text-slate-500">
                If you remember or have a record of what you paid for this historical month, fill it below. 
                If you don't remember, leave it empty to avoid corrupting financial integrity.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Paid Amount (NPR)</label>
                    <input type="number" step="0.01" min="0" name="payment_amount" placeholder="Leave empty if unknown" value="{{ old('payment_amount') }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Date</label>
                    <input type="date" name="payment_date" value="{{ old('payment_date') }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method</label>
                    <select name="payment_method" class="w-full text-sm rounded-lg border-slate-300 bg-white px-3 py-2 border">
                        <option value="Cash">Cash</option>
                        <option value="eSewa">eSewa</option>
                        <option value="Khalti">Khalti</option>
                        <option value="Bank Transfer">Bank Transfer</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Reference / Note</label>
                <input type="text" name="transaction_reference" placeholder="e.g. Paid in cash, receipt #401" value="{{ old('transaction_reference') }}" class="w-full text-sm rounded-lg border-slate-300 px-3 py-2 border">
            </div>
        </div>

        <!-- Notes & Submit -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Historical Notes / Memory Context</label>
                <textarea name="notes" rows="2" placeholder="e.g. Moved in during this month, water meter was under repair" class="w-full text-sm rounded-lg border-slate-300 p-3 border">{{ old('notes') }}</textarea>
            </div>

            <div class="pt-2 flex items-center justify-end gap-3">
                <a href="{{ route('historical.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">Cancel</a>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition">
                    Save Historical Record
                </button>
            </div>
        </div>

    </form>

</div>
@endsection
