@extends('layouts.app')

@section('title', 'Payments')
@section('page_title', 'Payment Transactions & Settlement Timing')

@section('content')
<div class="space-y-6">

    <!-- Payment Breakdown Summary Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Paid All Time</span>
            <div class="text-2xl font-extrabold text-emerald-600 mt-1">
                NPR {{ number_format($totalPaid, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">{{ $payments->total() }} total transactions</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">eSewa Payments</span>
            <div class="text-xl font-extrabold text-emerald-700 mt-1">
                NPR {{ number_format($esewaTotal, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Digital wallet</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Khalti Payments</span>
            <div class="text-xl font-extrabold text-purple-700 mt-1">
                NPR {{ number_format($khaltiTotal, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Digital wallet</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Bank Transfers</span>
            <div class="text-xl font-extrabold text-blue-700 mt-1">
                NPR {{ number_format($bankTotal, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Direct wire / IPS / Fonepay</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Cash Payments</span>
            <div class="text-xl font-extrabold text-amber-700 mt-1">
                NPR {{ number_format($cashTotal, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Handed to landlord</div>
        </div>
    </div>

    <!-- Filter and Action Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="method" class="text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                    <option value="">All Payment Methods</option>
                    <option value="eSewa" {{ request('method') === 'eSewa' ? 'selected' : '' }}>eSewa</option>
                    <option value="Khalti" {{ request('method') === 'Khalti' ? 'selected' : '' }}>Khalti</option>
                    <option value="Bank Transfer" {{ request('method') === 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="Cash" {{ request('method') === 'Cash' ? 'selected' : '' }}>Cash</option>
                    <option value="Other" {{ request('method') === 'Other' ? 'selected' : '' }}>Other</option>
                </select>
            </div>

            <div>
                <select name="monthly_record_id" class="text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                    <option value="">All Rental Months</option>
                    @foreach($monthlyRecords as $mr)
                        <option value="{{ $mr->id }}" {{ request('monthly_record_id') == $mr->id ? 'selected' : '' }}>
                            {{ $mr->dual_month_label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                Filter
            </button>

            @if(request()->hasAny(['method', 'monthly_record_id']))
                <a href="{{ route('payments.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-medium">
                    Reset
                </a>
            @endif
        </form>

        <button type="button" onclick="openGlobalPaymentModal()" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Record New Payment</span>
        </button>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3.5 px-4">Payment Date (AD & BS)</th>
                        <th class="py-3.5 px-4">Applied Rental Month</th>
                        <th class="py-3.5 px-4">Payment Timing</th>
                        <th class="py-3.5 px-4">Method</th>
                        <th class="py-3.5 px-4">Reference</th>
                        <th class="py-3.5 px-4">Notes</th>
                        <th class="py-3.5 px-4">Amount</th>
                        <th class="py-3.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($payments as $pay)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-semibold text-slate-800">
                                <div>{{ $pay->payment_date->format('M d, Y') }}</div>
                                <div class="text-[10px] text-teal-700">{{ $pay->bs_payment_date }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-700">
                                @if($pay->monthlyRecord)
                                    <a href="{{ route('monthly-records.show', $pay->monthlyRecord) }}" class="hover:text-teal-600 transition">
                                        {{ $pay->monthlyRecord->dual_month_label }}
                                    </a>
                                @else
                                    <span class="text-slate-400">General</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($pay->days_late > 0)
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                        +{{ $pay->days_late }}d late
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        On-time
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $pay->method_badge_class }}">
                                    {{ $pay->payment_method }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-[11px] text-slate-600">
                                {{ $pay->transaction_reference ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-500">
                                {{ $pay->notes ?? '—' }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-emerald-600 text-sm">
                                NPR {{ number_format($pay->amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" onclick="confirmDelete('{{ route('payments.destroy', $pay) }}', 'Delete payment of NPR {{ number_format($pay->amount, 2) }}? Balance for {{ $pay->monthlyRecord ? $pay->monthlyRecord->formatted_month : 'this month' }} will be recalculated.')" class="p-1.5 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-lg transition" title="Delete Payment">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400">
                                <i data-lucide="wallet" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="font-bold text-slate-700 text-sm">No payment records found</p>
                                <p class="text-xs text-slate-400 mt-1">Record rent and utility installments here.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payments->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $payments->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
