@extends('layouts.app')

@section('title', $monthlyRecord->formatted_month . ' Statement')
@section('page_title', $monthlyRecord->formatted_month . ' Rental Details')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
        <div class="flex items-center gap-3">
            <a href="{{ route('monthly-records.index') }}" class="p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-200 transition">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="text-xl font-bold text-slate-900">{{ $monthlyRecord->formatted_month }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $monthlyRecord->status_badge_class }}">
                        {{ $monthlyRecord->status_label }}
                    </span>
                    @if($monthlyRecord->is_historical)
                        <span class="px-2 py-0.5 rounded text-[10px] bg-slate-100 text-slate-500 border border-slate-200 font-medium">Historical Record</span>
                    @endif
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Recorded for period: {{ $monthlyRecord->record_date->format('F 01, Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" onclick="window.print()" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="printer" class="w-4 h-4 text-slate-500"></i>
                <span>Print</span>
            </button>
            <a href="{{ route('monthly-records.edit', $monthlyRecord) }}" class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-200 rounded-lg shadow-sm transition flex items-center gap-1.5">
                <i data-lucide="edit-3" class="w-4 h-4 text-slate-500"></i>
                <span>Edit</span>
            </a>
            <button type="button" onclick="confirmDelete('{{ route('monthly-records.destroy', $monthlyRecord) }}', 'Delete {{ $monthlyRecord->formatted_month }}? This will remove all associated payment logs.')" class="px-3 py-1.5 text-xs font-semibold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-lg transition flex items-center gap-1.5">
                <i data-lucide="trash-2" class="w-4 h-4"></i>
                <span>Delete</span>
            </button>
        </div>
    </div>

    <!-- Print-only invoice header -->
    <div class="hidden print-only bg-white p-6 border-b border-slate-300">
        <h1 class="text-2xl font-bold text-slate-900">RentMate Statement</h1>
        <p class="text-sm text-slate-600">Month: {{ $monthlyRecord->formatted_month }}</p>
        <p class="text-xs text-slate-500">Property: {{ $settings->property_address ?? 'Kathmandu, Nepal' }}</p>
    </div>

    <!-- Financial Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Monthly Expenses</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">
                NPR {{ number_format($monthlyRecord->total_expenses, 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Rent + Electricity + Water + Other</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Paid Amount</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">
                NPR {{ number_format($monthlyRecord->total_paid, 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">{{ $monthlyRecord->payments->count() }} transaction(s) recorded</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Outstanding Balance</span>
            <div class="text-2xl font-extrabold {{ $monthlyRecord->balance > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                NPR {{ number_format($monthlyRecord->balance, 2) }}
            </div>
            <div class="text-[11px] mt-1 font-semibold {{ $monthlyRecord->balance > 0 ? 'text-rose-500' : 'text-emerald-500' }}">
                {{ $monthlyRecord->balance > 0 ? 'Payment Pending' : 'Fully Settled' }}
            </div>
        </div>
    </div>

    <!-- Cost Breakdown Details -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 bg-slate-50/60 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="receipt" class="w-4 h-4 text-teal-600"></i>
                <span>Cost & Expense Breakdown</span>
            </h3>
            <span class="text-xs text-slate-400">All figures in Nepalese Rupee (NPR)</span>
        </div>

        <div class="divide-y divide-slate-100 text-sm">
            <!-- Rent Item -->
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center shrink-0">
                        <i data-lucide="home" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800">Monthly House Rent</div>
                        <div class="text-xs text-slate-400">Contractual fixed rental rate</div>
                    </div>
                </div>
                <div class="font-bold text-slate-900">
                    NPR {{ number_format($monthlyRecord->rent_amount, 2) }}
                </div>
            </div>

            <!-- Electricity Item -->
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="zap" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800">Electricity Bill</div>
                        <div class="text-xs text-slate-400">
                            Meter: {{ $monthlyRecord->prev_electricity_reading }} → {{ $monthlyRecord->curr_electricity_reading }} 
                            ({{ $monthlyRecord->electricity_units }} units @ NPR {{ $monthlyRecord->electricity_rate }})
                        </div>
                    </div>
                </div>
                <div class="font-bold text-slate-900">
                    NPR {{ number_format($monthlyRecord->electricity_amount, 2) }}
                </div>
            </div>

            <!-- Water Item -->
            <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center shrink-0">
                        <i data-lucide="droplets" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <div class="font-bold text-slate-800">Water Bill (Your Share)</div>
                        <div class="text-xs text-slate-400">
                            {{ $monthlyRecord->tenant_water_percentage }}% of total NPR {{ number_format($monthlyRecord->total_water_bill, 2) }} (Owner share: NPR {{ number_format($monthlyRecord->owner_water_share, 2) }})
                        </div>
                    </div>
                </div>
                <div class="font-bold text-slate-900">
                    NPR {{ number_format($monthlyRecord->water_amount, 2) }}
                </div>
            </div>

            <!-- Additional Items if any -->
            @foreach($monthlyRecord->additionalExpenses as $expense)
                <div class="p-4 flex items-center justify-between hover:bg-slate-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <i data-lucide="layers" class="w-4 h-4"></i>
                        </div>
                        <div>
                            <div class="font-bold text-slate-800">{{ $expense->title }}</div>
                            <div class="text-xs text-slate-400">Category: {{ $expense->category }}</div>
                        </div>
                    </div>
                    <div class="font-bold text-slate-900">
                        NPR {{ number_format($expense->amount, 2) }}
                    </div>
                </div>
            @endforeach

            <!-- Total Bar -->
            <div class="p-4 bg-slate-50 flex items-center justify-between font-bold text-base">
                <span class="text-slate-800">Grand Total Expenses</span>
                <span class="text-teal-700">NPR {{ number_format($monthlyRecord->total_expenses, 2) }}</span>
            </div>
        </div>

        @if($monthlyRecord->notes)
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 text-xs text-slate-600">
                <strong class="text-slate-700">Notes:</strong> {{ $monthlyRecord->notes }}
            </div>
        @endif
    </div>

    <!-- Payments Section -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4 text-emerald-600"></i>
                    <span>Payments for {{ $monthlyRecord->formatted_month }}</span>
                </h3>
                <p class="text-xs text-slate-500">Supports single and multiple installment payments</p>
            </div>
            <button type="button" onclick="openGlobalPaymentModal({{ $monthlyRecord->id }})" class="no-print inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Add Payment
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Payment Date</th>
                        <th class="py-3 px-4">Payment Method</th>
                        <th class="py-3 px-4">Transaction Ref</th>
                        <th class="py-3 px-4">Notes</th>
                        <th class="py-3 px-4">Amount</th>
                        <th class="py-3 px-4 text-right no-print">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($monthlyRecord->payments as $payment)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3 px-4 font-semibold text-slate-800">
                                {{ $payment->payment_date->format('M d, Y') }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold border {{ $payment->method_badge_class }}">
                                    {{ $payment->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                                {{ $payment->transaction_reference ?? '—' }}
                            </td>
                            <td class="py-3 px-4 text-slate-500">
                                {{ $payment->notes ?? '—' }}
                            </td>
                            <td class="py-3 px-4 font-bold text-emerald-600 text-sm">
                                NPR {{ number_format($payment->amount, 2) }}
                            </td>
                            <td class="py-3 px-4 text-right no-print">
                                <button type="button" onclick="confirmDelete('{{ route('payments.destroy', $payment) }}', 'Delete payment of NPR {{ number_format($payment->amount, 2) }}? Balance will be adjusted.')" class="text-rose-500 hover:text-rose-700 p-1">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <i data-lucide="credit-card" class="w-6 h-6 mx-auto text-slate-300 mb-1"></i>
                                <span>No payment transactions logged for this month yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
