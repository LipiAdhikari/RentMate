@extends('layouts.app')

@section('title', 'New Monthly Record')
@section('page_title', 'Create Monthly Rental Record')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-bold text-slate-800">New Rental Month</h2>
            <p class="text-xs text-slate-500">Supports both Nepali Bikram Sambat (BS) & English (AD) calendar periods.</p>
        </div>
        <a href="{{ route('monthly-records.index') }}" class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
            Back to Records
        </a>
    </div>

    <form action="{{ route('monthly-records.store') }}" method="POST" id="monthly-record-form" class="space-y-6">
        @csrf

        <!-- 1. Month & Rent Card with Dual BS / AD -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="calendar" class="w-4 h-4 text-teal-600"></i>
                    <span>1. Rental Month Period (Nepali BS & English AD)</span>
                </h3>
                <span class="text-xs text-teal-600 font-semibold">🇳🇵 Dual Calendar Enabled</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Nepali Month -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nepali Month (महिना) *</label>
                    <select name="bs_month" id="bs_month" onchange="syncFromNepaliMonth()" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border font-medium">
                        @foreach($nepaliMonths as $num => $info)
                            <option value="{{ $num }}" {{ old('bs_month', $defaultBsMonth) == $num ? 'selected' : '' }}>
                                {{ $info['en'] }} ({{ $info['np'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Nepali Year -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nepali Year (वि.सं.) *</label>
                    <select name="bs_year" id="bs_year" onchange="syncFromNepaliMonth()" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border font-medium">
                        @for($by = 2085; $by >= 2080; $by--)
                            <option value="{{ $by }}" {{ old('bs_year', $defaultBsYear) == $by ? 'selected' : '' }}>
                                {{ $by }} BS ({{ \App\Services\NepaliCalendar::toNepaliNumerals($by) }})
                            </option>
                        @endfor
                    </select>
                </div>

                <!-- AD Month -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">English Month (AD) *</label>
                    <select name="month" id="month" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
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

                <!-- AD Year -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">English Year (AD) *</label>
                    <select name="year" id="year" required class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                        @for($y = date('Y') + 1; $y >= 2024; $y--)
                            <option value="{{ $y }}" {{ old('year', $defaultYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Monthly House Rent (NPR) *</label>
                <input type="number" step="0.01" min="0" name="rent_amount" id="rent_amount" required value="{{ old('rent_amount', $settings->rent_amount) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
            </div>
        </div>

        <!-- 2. Electricity Meter Readings Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                    <span>2. Electricity Meter Tracker</span>
                </h3>
                <span class="text-xs text-slate-400">Formula: (Current - Previous) × Rate</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Previous Meter Reading *</label>
                    <input type="number" step="0.01" min="0" name="prev_electricity_reading" id="prev_electricity_reading" required value="{{ old('prev_electricity_reading', $defaultPrevReading) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    <span class="text-[10px] text-slate-400">Last month's ending reading</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Current Meter Reading *</label>
                    <input type="number" step="0.01" min="0" name="curr_electricity_reading" id="curr_electricity_reading" required value="{{ old('curr_electricity_reading', $defaultPrevReading) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    <span id="reading-warning" class="text-[10px] text-rose-500 hidden">Current reading cannot be less than previous!</span>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rate per Unit (NPR) *</label>
                    <input type="number" step="0.01" min="0" name="electricity_rate" id="electricity_rate" required value="{{ old('electricity_rate', $settings->electricity_rate) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                    <span class="text-[10px] text-slate-400">Configured: NPR {{ $settings->electricity_rate }}/unit</span>
                </div>
            </div>

            <!-- Live Electricity Calculation Preview -->
            <div class="bg-amber-50/60 border border-amber-200/70 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between text-xs gap-3">
                <div class="flex items-center gap-2 text-amber-900">
                    <i data-lucide="calculator" class="w-4 h-4 text-amber-600"></i>
                    <span>Calculated Units: <strong id="calc-units" class="text-sm">0</strong> kWh</span>
                </div>
                <div class="text-amber-900 font-bold text-sm">
                    Electricity Bill: NPR <span id="calc-electricity-bill">0.00</span>
                </div>
            </div>
        </div>

        <!-- 3. Water Bill Sharing Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="droplets" class="w-4 h-4 text-sky-500"></i>
                    <span>3. Water Bill (Shared with Owner)</span>
                </h3>
                <span class="text-xs text-slate-400">Default: 50% Tenant / 50% Owner</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Total Water Bill from Authority (NPR) *</label>
                    <input type="number" step="0.01" min="0" name="total_water_bill" id="total_water_bill" required value="{{ old('total_water_bill', 0.00) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Your Share Percentage (%) *</label>
                    <input type="number" step="0.01" min="0" max="100" name="tenant_water_percentage" id="tenant_water_percentage" required value="{{ old('tenant_water_percentage', $settings->water_share_percentage) }}" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-2 border">
                </div>
            </div>

            <!-- Live Water Calculation Preview -->
            <div class="bg-sky-50/60 border border-sky-200/70 rounded-xl p-4 flex flex-col sm:flex-row items-center justify-between text-xs gap-3">
                <div class="text-sky-900">
                    Owner's Share: <strong id="calc-owner-water">NPR 0.00</strong>
                </div>
                <div class="text-sky-900 font-bold text-sm">
                    Your Share to Pay: NPR <span id="calc-tenant-water">0.00</span>
                </div>
            </div>
        </div>

        <!-- 4. Additional Expenses Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-purple-500"></i>
                    <span>4. Additional Charges (Waste, Maintenance, Internet)</span>
                </h3>
                <button type="button" onclick="addExpenseRow()" class="inline-flex items-center gap-1 text-xs font-semibold text-purple-600 hover:text-purple-700 bg-purple-50 px-2.5 py-1 rounded-lg transition">
                    <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Charge
                </button>
            </div>

            <div id="expenses-container" class="space-y-3">
                <!-- Dynamically populated rows -->
            </div>
        </div>

        <!-- 5. Notes & Grand Total Card -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Notes / Remarks (Optional)</label>
                <textarea name="notes" rows="2" placeholder="e.g. Festival month, meter checked on 1st" class="w-full text-sm rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 p-3 border">{{ old('notes') }}</textarea>
            </div>

            <!-- Grand Total Summary Banner -->
            <div class="bg-slate-900 text-white rounded-2xl p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-slate-400 uppercase tracking-wider block">Total Estimated Monthly Expenses</span>
                    <div class="text-3xl font-extrabold text-teal-400 mt-1">
                        NPR <span id="calc-grand-total">0.00</span>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('monthly-records.index') }}" class="px-4 py-2.5 text-xs font-semibold text-slate-300 hover:text-white bg-slate-800 hover:bg-slate-700 rounded-xl transition">
                        Cancel
                    </a>
                    <button type="submit" id="submit-btn" class="px-5 py-2.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-500 rounded-xl shadow-md shadow-teal-900/30 transition flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Save Record</span>
                    </button>
                </div>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
<script>
    function syncFromNepaliMonth() {
        const bsMonth = parseInt(document.getElementById('bs_month')?.value) || 8;
        const bsYear = parseInt(document.getElementById('bs_year')?.value) || 2083;

        // Approximate map from BS to AD month
        // Baisakh (1) -> April (4), Jestha (2) -> May (5), ..., Mangsir (8) -> November (11)
        let adMonth = bsMonth + 3;
        let adYear = bsYear - 57;
        if (adMonth > 12) {
            adMonth -= 12;
            adYear += 1;
        }

        const monthSelect = document.getElementById('month');
        const yearSelect = document.getElementById('year');
        if (monthSelect) monthSelect.value = adMonth;
        if (yearSelect) yearSelect.value = adYear;
    }

    let expenseRowIndex = 0;

    function addExpenseRow(title = '', amount = '', category = 'Waste') {
        const container = document.getElementById('expenses-container');
        const row = document.createElement('div');
        row.className = 'grid grid-cols-12 gap-3 items-center expense-row bg-slate-50 p-2.5 rounded-xl border border-slate-200';
        row.id = `expense-row-${expenseRowIndex}`;
        row.innerHTML = `
            <div class="col-span-5">
                <input type="text" name="expenses[${expenseRowIndex}][title]" value="${title}" placeholder="Charge Title" required class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-1.5 border">
            </div>
            <div class="col-span-3">
                <input type="number" step="0.01" min="0" name="expenses[${expenseRowIndex}][amount]" value="${amount}" placeholder="Amount (NPR)" required oninput="calculateAll()" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 px-3 py-1.5 border expense-amount-input">
            </div>
            <div class="col-span-3">
                <select name="expenses[${expenseRowIndex}][category]" class="w-full text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-2 py-1.5 border">
                    <option value="Waste" ${category === 'Waste' ? 'selected' : ''}>Waste</option>
                    <option value="Maintenance" ${category === 'Maintenance' ? 'selected' : ''}>Maintenance</option>
                    <option value="Internet" ${category === 'Internet' ? 'selected' : ''}>Internet</option>
                    <option value="Other" ${category === 'Other' ? 'selected' : ''}>Other</option>
                </select>
            </div>
            <div class="col-span-1 text-right">
                <button type="button" onclick="removeExpenseRow(${expenseRowIndex})" class="text-rose-500 hover:text-rose-700 p-1">
                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        expenseRowIndex++;
        if (window.lucide) window.lucide.createIcons();
        calculateAll();
    }

    function removeExpenseRow(idx) {
        const row = document.getElementById(`expense-row-${idx}`);
        if (row) {
            row.remove();
            calculateAll();
        }
    }

    function calculateAll() {
        const rent = parseFloat(document.getElementById('rent_amount')?.value) || 0;
        const prevReading = parseFloat(document.getElementById('prev_electricity_reading')?.value) || 0;
        const currReading = parseFloat(document.getElementById('curr_electricity_reading')?.value) || 0;
        const elecRate = parseFloat(document.getElementById('electricity_rate')?.value) || 0;
        const totalWater = parseFloat(document.getElementById('total_water_bill')?.value) || 0;
        const waterPct = parseFloat(document.getElementById('tenant_water_percentage')?.value) || 0;

        let units = 0;
        const warning = document.getElementById('reading-warning');

        if (currReading < prevReading) {
            if (warning) warning.classList.remove('hidden');
            units = 0;
        } else {
            if (warning) warning.classList.add('hidden');
            units = currReading - prevReading;
        }

        const elecBill = units * elecRate;
        document.getElementById('calc-units').innerText = units.toFixed(2);
        document.getElementById('calc-electricity-bill').innerText = elecBill.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        const tenantWater = totalWater * (waterPct / 100);
        const ownerWater = totalWater - tenantWater;
        document.getElementById('calc-tenant-water').innerText = tenantWater.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
        document.getElementById('calc-owner-water').innerText = 'NPR ' + ownerWater.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});

        let otherTotal = 0;
        document.querySelectorAll('.expense-amount-input').forEach(input => {
            otherTotal += parseFloat(input.value) || 0;
        });

        const grandTotal = rent + elecBill + tenantWater + otherTotal;
        document.getElementById('calc-grand-total').innerText = grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }

    document.addEventListener('DOMContentLoaded', function () {
        ['rent_amount', 'prev_electricity_reading', 'curr_electricity_reading', 'electricity_rate', 'total_water_bill', 'tenant_water_percentage'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('input', calculateAll);
        });

        calculateAll();
    });
</script>
@endpush
