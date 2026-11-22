@extends('layouts.app')

@section('title', 'Electricity Tracker')
@section('page_title', 'Flexible Electricity Billing & Meter Tracker')

@section('content')
<div class="space-y-6">

    <!-- Top Action & Filter Bar -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Electricity Bills & Meter Readings</h2>
            <p class="text-xs text-slate-500 mt-0.5">Flexible multi-month billing periods, independent meter reading logs, and payment tracking</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button type="button" onclick="openAddBillModal()"
                class="px-4 py-2.5 rounded-xl bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs shadow-md shadow-teal-700/20 transition-all flex items-center justify-center gap-2 active:scale-95">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Electricity Bill</span>
            </button>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Electricity Cost</span>
                <div class="w-7 h-7 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                    <i data-lucide="zap" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">
                NPR {{ number_format($totalCost, 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1 flex items-center gap-1.5">
                <span>Across {{ count($bills) }} billing period(s)</span>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Total Units Consumed</span>
                <div class="w-7 h-7 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="activity" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-teal-600 mt-1">
                {{ number_format($totalUnits, 1) }} kWh
            </div>
            <div class="text-[11px] text-slate-400 mt-1">
                Avg: {{ number_format($avgUnits, 1) }} kWh / bill
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Payments & Balance</span>
                <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold {{ $totalBalance > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                NPR {{ number_format($totalBalance, 2) }}
            </div>
            <div class="text-[11px] text-slate-500 mt-1">
                Paid: NPR {{ number_format($totalPaid, 2) }} | {{ $unpaidCount }} unpaid bill(s)
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between text-slate-500 mb-1">
                <span class="text-xs font-semibold uppercase tracking-wider">Active Tariff Rate</span>
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="tag" class="w-4 h-4"></i>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-slate-800 mt-1">
                NPR {{ number_format($settings->electricity_rate, 2) }}
            </div>
            <div class="text-[11px] text-slate-400 mt-1">Per unit / kWh (configurable in Settings)</div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-sm">
        <form action="{{ route('electricity.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Payment Status</label>
                <select name="status" class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                    <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid Bills</option>
                    <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">From Start Date</label>
                <input type="date" name="from_date" value="{{ request('from_date') }}"
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">To End Date</label>
                <input type="date" name="to_date" value="{{ request('to_date') }}"
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div class="flex items-center gap-2">
                <button type="submit"
                    class="flex-1 py-2 px-3 rounded-xl bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs transition flex items-center justify-center gap-1.5">
                    <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                    <span>Apply Filters</span>
                </button>
                @if(request()->hasAny(['status', 'from_date', 'to_date']))
                <a href="{{ route('electricity.index') }}"
                    class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition" title="Clear Filters">
                    Clear
                </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Flexible Electricity Bills Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/60">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="receipt" class="w-4 h-4 text-teal-600"></i>
                    <span>Flexible Electricity Bills</span>
                </h3>
                <p class="text-xs text-slate-500">Bills covering 1, 2, 3, 4, or 5+ months with standalone payment tracking & monthly allocations</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg bg-teal-50 text-teal-700 border border-teal-200/60 self-start sm:self-auto">
                {{ count($bills) }} Bill(s) Recorded
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Billing Period</th>
                        <th class="py-3 px-4">Meter Readings</th>
                        <th class="py-3 px-4">Units Consumed</th>
                        <th class="py-3 px-4">Rate</th>
                        <th class="py-3 px-4">Total Amount</th>
                        <th class="py-3 px-4">Paid / Balance</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Allocations</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($bills as $bill)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm text-slate-800">{{ $bill->formatted_period }}</span>
                                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-md bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $bill->months_count }} Mo
                                    </span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-normal">
                                    {{ Carbon\Carbon::parse($bill->billing_period_start)->format('M d, Y') }} – {{ Carbon\Carbon::parse($bill->billing_period_end)->format('M d, Y') }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                <div><span class="text-slate-400">Prev:</span> {{ number_format($bill->previous_meter_reading, 2) }}</div>
                                <div><span class="text-slate-400">Curr:</span> <strong class="text-slate-800">{{ number_format($bill->current_meter_reading, 2) }}</strong></div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-lg font-bold text-xs bg-amber-50 text-amber-700 border border-amber-200/80 inline-block">
                                    {{ number_format($bill->units_consumed, 1) }} kWh
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600 font-medium">
                                NPR {{ number_format($bill->rate_per_unit, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900 text-sm">
                                NPR {{ number_format($bill->total_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-emerald-600">Paid: NPR {{ number_format($bill->paid_amount, 2) }}</div>
                                <div class="font-bold {{ $bill->balance > 0 ? 'text-rose-600' : 'text-slate-400' }}">
                                    Bal: NPR {{ number_format($bill->balance, 2) }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                @if($bill->status === 'paid')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Fully Paid
                                    </span>
                                @elseif($bill->status === 'partially_paid')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800 border border-amber-200">
                                        Partially Paid
                                    </span>
                                @elseif($bill->status === 'overpaid')
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-purple-100 text-purple-800 border border-purple-200">
                                        Overpaid
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                        Unpaid
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                @php
                                    $allocatedTotal = $bill->allocatedTotal();
                                    $allocCount = $bill->allocations->count();
                                @endphp
                                @if($allocCount > 0)
                                    <div class="text-xs font-semibold text-teal-700">
                                        NPR {{ number_format($allocatedTotal, 2) }} / {{ number_format($bill->total_amount, 2) }}
                                    </div>
                                    <div class="text-[10px] text-slate-500">Across {{ $allocCount }} month(s)</div>
                                @else
                                    <span class="text-[11px] text-slate-400 italic">Unallocated</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    @if($bill->balance > 0)
                                    <button type="button" onclick="openPaymentModal({{ $bill->id }}, '{{ $bill->formatted_period }}', {{ $bill->balance }})"
                                        class="px-2.5 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition" title="Pay Electricity Bill">
                                        Pay
                                    </button>
                                    @endif
                                    <button type="button" onclick="openEditBillModal({{ json_encode($bill) }})"
                                        class="px-2 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition" title="Edit Bill">
                                        <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    </button>
                                    <form action="{{ route('electricity.bills.destroy', $bill) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this electricity bill? This will also remove associated payments and monthly allocations.')" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2 py-1.5 text-xs font-semibold text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Delete Bill">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-10 text-center text-slate-400">
                                <i data-lucide="zap-off" class="w-8 h-8 mx-auto text-slate-300 mb-2"></i>
                                <div class="font-medium text-slate-600">No flexible electricity bills found.</div>
                                <div class="text-xs text-slate-400 mt-0.5">Click 'Add Electricity Bill' above to record bills for 1 to 5+ months.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Electricity Trends Chart -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Historical Electricity Consumption & Cost Trends</h3>
                <p class="text-xs text-slate-500">Visual trend of meter units consumed (kWh) and total bill costs per billing period</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 rounded-lg border border-amber-200/60">Units & Cost</span>
        </div>
        <div class="h-64">
            <canvas id="elecFullChart"></canvas>
        </div>
    </div>

    <!-- Legacy Monthly Readings Log (Backward Compatibility) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="font-bold text-slate-700 text-xs flex items-center gap-2 uppercase tracking-wider">
                    <i data-lucide="history" class="w-3.5 h-3.5 text-slate-400"></i>
                    <span>Monthly Record Meter Logs (Synchronized)</span>
                </h3>
            </div>
            <span class="text-[11px] text-slate-400">Synced with rental monthly records</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/60 border-b border-slate-200 text-slate-400 uppercase tracking-wider font-semibold">
                        <th class="py-2.5 px-4">Rental Month</th>
                        <th class="py-2.5 px-4">Meter Readings</th>
                        <th class="py-2.5 px-4">Units</th>
                        <th class="py-2.5 px-4">Rate</th>
                        <th class="py-2.5 px-4">Electricity Cost</th>
                        <th class="py-2.5 px-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-600">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-2.5 px-4 font-bold text-slate-800">
                                <a href="{{ route('monthly-records.show', $rec) }}" class="hover:text-teal-600 transition">
                                    {{ $rec->formatted_month }}
                                </a>
                            </td>
                            <td class="py-2.5 px-4 font-mono">
                                {{ number_format($rec->prev_electricity_reading, 2) }} &rarr; {{ number_format($rec->curr_electricity_reading, 2) }}
                            </td>
                            <td class="py-2.5 px-4">
                                <span class="px-2 py-0.5 rounded font-bold text-[11px] bg-amber-50 text-amber-700 border border-amber-200/60">
                                    {{ $rec->electricity_units }} kWh
                                </span>
                            </td>
                            <td class="py-2.5 px-4">NPR {{ number_format($rec->electricity_rate, 2) }}</td>
                            <td class="py-2.5 px-4 font-bold text-slate-900">
                                NPR {{ number_format($rec->electricity_amount, 2) }}
                            </td>
                            <td class="py-2.5 px-4 text-right">
                                <button type="button" onclick="openUpdateModal({{ $rec->id }}, '{{ $rec->formatted_month }}', {{ $rec->prev_electricity_reading }}, {{ $rec->curr_electricity_reading }}, {{ $rec->electricity_rate }})"
                                    class="px-2 py-1 text-[11px] font-semibold text-slate-600 hover:text-teal-700 bg-slate-100 hover:bg-slate-200 rounded transition">
                                    Quick Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-slate-400">No monthly logs recorded.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ============================================
     MODAL 1: ADD ELECTRICITY BILL
============================================ -->
<div id="add-bill-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                    <i data-lucide="zap" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Add Electricity Bill</h3>
                    <p class="text-xs text-slate-500">Flexible multi-month period with live calculation math</p>
                </div>
            </div>
            <button type="button" onclick="closeAddBillModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('electricity.bills.store') }}" method="POST" id="add-bill-form" class="space-y-4">
            @csrf

            <!-- Billing Period Start and End Dates (Independent) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Period Start *</label>
                    <input type="date" name="billing_period_start" id="add_start_date" required onchange="updateAddBillPeriodPreview()"
                        value="{{ date('Y-m-01') }}"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Period End *</label>
                    <input type="date" name="billing_period_end" id="add_end_date" required onchange="updateAddBillPeriodPreview()"
                        value="{{ date('Y-m-t') }}"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>

            <!-- Period Preview Badge -->
            <div class="p-2.5 rounded-xl bg-teal-50/80 border border-teal-200/60 text-teal-800 text-xs font-medium flex items-center justify-between">
                <span class="flex items-center gap-1.5">
                    <i data-lucide="calendar" class="w-4 h-4 text-teal-600"></i>
                    <strong id="add_period_label">Current Month</strong>
                </span>
                <span id="add_period_months_badge" class="px-2 py-0.5 rounded-md bg-teal-600 text-white text-[10px] font-bold">1 Month</span>
            </div>

            <!-- Readings & Rate -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Previous Reading *</label>
                    <input type="number" step="0.01" min="0" name="previous_meter_reading" id="add_prev_reading" required
                        value="{{ $latestRecord ? $latestRecord->curr_electricity_reading : '0.00' }}"
                        oninput="calculateAddBillMath()"
                        placeholder="0.00"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-mono text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Current Reading *</label>
                    <input type="number" step="0.01" min="0" name="current_meter_reading" id="add_curr_reading" required
                        value="{{ $latestRecord ? $latestRecord->curr_electricity_reading : '0.00' }}"
                        oninput="calculateAddBillMath()"
                        placeholder="0.00"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-mono font-bold text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rate (NPR/unit) *</label>
                    <input type="number" step="0.01" min="0" name="rate_per_unit" id="add_rate" required
                        value="{{ $settings->electricity_rate }}"
                        oninput="calculateAddBillMath()"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-mono text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>

            <!-- Live Calculation Preview Card -->
            <div class="p-3.5 rounded-xl bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Units Consumed</div>
                    <div class="text-base font-extrabold text-amber-400 font-mono" id="add_preview_units">0.00 kWh</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Total Electricity Cost</div>
                    <div class="text-lg font-extrabold text-teal-400 font-mono" id="add_preview_total">NPR 0.00</div>
                </div>
            </div>

            <!-- Optional Multi-Month Allocation Section -->
            <div class="border border-slate-200 rounded-2xl p-4 bg-slate-50/40">
                <div class="flex items-center justify-between mb-2">
                    <div>
                        <span class="text-xs font-bold text-slate-800 block">Optional Monthly Expense Allocation</span>
                        <span class="text-[11px] text-slate-500">Allocate parts of this bill to specific monthly rental records</span>
                    </div>
                    <button type="button" onclick="distributeEvenlyAddModal()" class="text-[11px] font-bold text-teal-600 hover:text-teal-700 bg-teal-50 px-2 py-1 rounded-lg border border-teal-200">
                        Distribute Evenly
                    </button>
                </div>

                <div class="space-y-2 max-h-40 overflow-y-auto pr-1" id="add_allocations_container">
                    @forelse($monthlyRecords as $idx => $mRec)
                        <div class="flex items-center justify-between gap-3 text-xs p-2 rounded-xl bg-white border border-slate-100">
                            <span class="font-medium text-slate-700">{{ $mRec->formatted_month }}</span>
                            <div class="flex items-center gap-1.5">
                                <span class="text-slate-400 text-[11px]">NPR</span>
                                <input type="hidden" name="allocations[{{ $idx }}][monthly_record_id]" value="{{ $mRec->id }}">
                                <input type="number" step="0.01" min="0" name="allocations[{{ $idx }}][allocated_amount]"
                                    class="add_alloc_input w-28 px-2.5 py-1 text-xs rounded-lg border border-slate-200 text-right font-mono"
                                    placeholder="0.00" oninput="checkAllocationsAddMath()">
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-2 text-xs text-slate-400">No monthly records available for allocation.</div>
                    @endforelse
                </div>

                <div class="mt-2 text-[11px] flex items-center justify-between text-slate-600 font-medium">
                    <span>Total Allocated: <strong id="add_total_alloc_display" class="font-mono text-slate-900">NPR 0.00</strong></span>
                    <span id="add_alloc_warning" class="text-rose-600 font-bold hidden">Exceeds total bill!</span>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Notes (Optional)</label>
                <textarea name="notes" rows="2" placeholder="e.g. Combined 4-month meter settling for Dashain & winter period..."
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeAddBillModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                <button type="submit" id="add_bill_submit_btn" class="px-5 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-md transition">Save Bill</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     MODAL 2: EDIT ELECTRICITY BILL
============================================ -->
<div id="edit-bill-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 my-8">
        <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-5">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                    <i data-lucide="edit-3" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Edit Electricity Bill</h3>
                    <p class="text-xs text-slate-500" id="edit_modal_subtitle">Update billing period and meter readings</p>
                </div>
            </div>
            <button type="button" onclick="closeEditBillModal()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form method="POST" id="edit-bill-form" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Period Start *</label>
                    <input type="date" name="billing_period_start" id="edit_start_date" required onchange="calculateEditBillMath()"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Billing Period End *</label>
                    <input type="date" name="billing_period_end" id="edit_end_date" required onchange="calculateEditBillMath()"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Previous Reading *</label>
                    <input type="number" step="0.01" min="0" name="previous_meter_reading" id="edit_prev_reading" required
                        oninput="calculateEditBillMath()"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-mono text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Current Reading *</label>
                    <input type="number" step="0.01" min="0" name="current_meter_reading" id="edit_curr_reading" required
                        oninput="calculateEditBillMath()"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-mono font-bold text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Rate (NPR/unit) *</label>
                    <input type="number" step="0.01" min="0" name="rate_per_unit" id="edit_rate" required
                        oninput="calculateEditBillMath()"
                        class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-mono text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                </div>
            </div>

            <!-- Live Preview -->
            <div class="p-3.5 rounded-xl bg-slate-900 text-white flex items-center justify-between">
                <div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Units Consumed</div>
                    <div class="text-base font-extrabold text-amber-400 font-mono" id="edit_preview_units">0.00 kWh</div>
                </div>
                <div class="text-right">
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Total Electricity Cost</div>
                    <div class="text-lg font-extrabold text-teal-400 font-mono" id="edit_preview_total">NPR 0.00</div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Notes</label>
                <textarea name="notes" id="edit_notes" rows="2"
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500"></textarea>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2.5">
                <button type="button" onclick="closeEditBillModal()" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl shadow-md transition">Update Bill</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     MODAL 3: RECORD ELECTRICITY PAYMENT
============================================ -->
<div id="payment-modal" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Record Electricity Payment</h3>
                    <p class="text-xs text-slate-500" id="pay_bill_title">Settlement for bill</p>
                </div>
            </div>
            <button type="button" onclick="closePaymentModal()" class="w-7 h-7 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form method="POST" id="payment-form" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Amount (NPR) *</label>
                <input type="number" step="0.01" min="0.01" name="amount" id="pay_amount" required
                    class="w-full text-sm rounded-xl border-slate-200 bg-slate-50/50 px-3.5 py-2.5 border font-bold text-slate-900 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Date *</label>
                <input type="date" name="payment_date" id="pay_date" required value="{{ date('Y-m-d') }}"
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Payment Method *</label>
                <select name="payment_method" required
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2.5 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
                    <option value="eSewa">eSewa</option>
                    <option value="Khalti">Khalti</option>
                    <option value="Bank Transfer">Bank Transfer</option>
                    <option value="Cash" selected>Cash</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Transaction Reference (Optional)</label>
                <input type="text" name="transaction_reference" placeholder="e.g. TXN-99821 or Cash receipt"
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Notes (Optional)</label>
                <input type="text" name="notes" placeholder="e.g. Cleared full bill with landlord"
                    class="w-full text-xs rounded-xl border-slate-200 bg-slate-50/50 px-3 py-2 border font-medium text-slate-800 focus:bg-white focus:ring-teal-500 focus:border-teal-500">
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                <button type="button" onclick="closePaymentModal()" class="px-3.5 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition">Cancel</button>
                <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md transition">Save Payment</button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================
     MODAL 4: LEGACY QUICK UPDATE READING
============================================ -->
<div id="elec-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                <span id="elec-modal-month">Update Reading</span>
            </h3>
            <button type="button" onclick="closeUpdateModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="elec-modal-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Previous Meter Reading *</label>
                <input type="number" step="0.01" min="0" name="prev_electricity_reading" id="modal_prev" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Current Meter Reading *</label>
                <input type="number" step="0.01" min="0" name="curr_electricity_reading" id="modal_curr" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border font-mono">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Electricity Rate (NPR/unit) *</label>
                <input type="number" step="0.01" min="0" name="electricity_rate" id="modal_rate" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border font-mono">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeUpdateModal()" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm">Save</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Month names array for readable labels
    const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

    function openAddBillModal() {
        document.getElementById('add-bill-modal').classList.remove('hidden');
        updateAddBillPeriodPreview();
        calculateAddBillMath();
        if (window.lucide) window.lucide.createIcons();
    }

    function closeAddBillModal() {
        document.getElementById('add-bill-modal').classList.add('hidden');
    }

    function updateAddBillPeriodPreview() {
        const startVal = document.getElementById('add_start_date').value;
        const endVal = document.getElementById('add_end_date').value;

        if (!startVal || !endVal) return;

        const s = new Date(startVal);
        const e = new Date(endVal);

        const sLabel = monthNames[s.getMonth()] + ' ' + s.getFullYear();
        const eLabel = monthNames[e.getMonth()] + ' ' + e.getFullYear();

        let label = sLabel === eLabel ? sLabel : (sLabel + ' – ' + eLabel);
        document.getElementById('add_period_label').innerText = label;

        let months = Math.max(1, (e.getFullYear() - s.getFullYear()) * 12 + (e.getMonth() - s.getMonth()) + 1);
        document.getElementById('add_period_months_badge').innerText = months + ' Month' + (months > 1 ? 's' : '');
    }

    function calculateAddBillMath() {
        const prev = parseFloat(document.getElementById('add_prev_reading').value) || 0;
        const curr = parseFloat(document.getElementById('add_curr_reading').value) || 0;
        const rate = parseFloat(document.getElementById('add_rate').value) || 0;

        const units = Math.max(0, curr - prev);
        const total = units * rate;

        document.getElementById('add_preview_units').innerText = units.toFixed(2) + ' kWh';
        document.getElementById('add_preview_total').innerText = 'NPR ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        checkAllocationsAddMath();
    }

    function checkAllocationsAddMath() {
        const inputs = document.querySelectorAll('.add_alloc_input');
        let totalAlloc = 0;
        inputs.forEach(inp => {
            const val = parseFloat(inp.value) || 0;
            totalAlloc += val;
        });

        document.getElementById('add_total_alloc_display').innerText = 'NPR ' + totalAlloc.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        const prev = parseFloat(document.getElementById('add_prev_reading').value) || 0;
        const curr = parseFloat(document.getElementById('add_curr_reading').value) || 0;
        const rate = parseFloat(document.getElementById('add_rate').value) || 0;
        const totalBill = Math.max(0, curr - prev) * rate;

        const warning = document.getElementById('add_alloc_warning');
        const submitBtn = document.getElementById('add_bill_submit_btn');

        if (totalAlloc > totalBill + 0.01) {
            warning.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            warning.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    function distributeEvenlyAddModal() {
        const prev = parseFloat(document.getElementById('add_prev_reading').value) || 0;
        const curr = parseFloat(document.getElementById('add_curr_reading').value) || 0;
        const rate = parseFloat(document.getElementById('add_rate').value) || 0;
        const totalBill = Math.max(0, curr - prev) * rate;

        const inputs = document.querySelectorAll('.add_alloc_input');
        if (inputs.length === 0 || totalBill <= 0) return;

        const perMonth = (totalBill / inputs.length).toFixed(2);
        inputs.forEach(inp => {
            inp.value = perMonth;
        });

        checkAllocationsAddMath();
    }

    function openEditBillModal(bill) {
        const modal = document.getElementById('edit-bill-modal');
        const form = document.getElementById('edit-bill-form');
        form.action = `/electricity/bills/${bill.id}`;

        document.getElementById('edit_start_date').value = bill.billing_period_start.split('T')[0];
        document.getElementById('edit_end_date').value = bill.billing_period_end.split('T')[0];
        document.getElementById('edit_prev_reading').value = bill.previous_meter_reading;
        document.getElementById('edit_curr_reading').value = bill.current_meter_reading;
        document.getElementById('edit_rate').value = bill.rate_per_unit;
        document.getElementById('edit_notes').value = bill.notes || '';

        calculateEditBillMath();
        modal.classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }

    function closeEditBillModal() {
        document.getElementById('edit-bill-modal').classList.add('hidden');
    }

    function calculateEditBillMath() {
        const prev = parseFloat(document.getElementById('edit_prev_reading').value) || 0;
        const curr = parseFloat(document.getElementById('edit_curr_reading').value) || 0;
        const rate = parseFloat(document.getElementById('edit_rate').value) || 0;

        const units = Math.max(0, curr - prev);
        const total = units * rate;

        document.getElementById('edit_preview_units').innerText = units.toFixed(2) + ' kWh';
        document.getElementById('edit_preview_total').innerText = 'NPR ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function openPaymentModal(billId, period, balance) {
        const modal = document.getElementById('payment-modal');
        const form = document.getElementById('payment-form');
        form.action = `/electricity/bills/${billId}/payments`;

        document.getElementById('pay_bill_title').innerText = 'Billing Period: ' + period;
        document.getElementById('pay_amount').value = balance > 0 ? balance.toFixed(2) : '0.00';

        modal.classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }

    function closePaymentModal() {
        document.getElementById('payment-modal').classList.add('hidden');
    }

    function openUpdateModal(id, month, prev, curr, rate) {
        const modal = document.getElementById('elec-modal');
        const form = document.getElementById('elec-modal-form');
        form.action = `/electricity/${id}`;
        document.getElementById('elec-modal-month').innerText = 'Update ' + month;
        document.getElementById('modal_prev').value = prev;
        document.getElementById('modal_curr').value = curr;
        document.getElementById('modal_rate').value = rate;
        modal.classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }

    function closeUpdateModal() {
        document.getElementById('elec-modal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (window.lucide) window.lucide.createIcons();

        const labels = @json($chartLabels);
        const units = @json($chartUnits);
        const costs = @json($chartCosts);

        const ctx = document.getElementById('elecFullChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            type: 'bar',
                            label: 'Units Consumed (kWh)',
                            data: units,
                            backgroundColor: '#f59e0b',
                            borderRadius: 6,
                            yAxisID: 'y'
                        },
                        {
                            type: 'line',
                            label: 'Electricity Cost (NPR)',
                            data: costs,
                            borderColor: '#0d9488',
                            backgroundColor: 'rgba(13, 148, 136, 0.1)',
                            borderWidth: 2.5,
                            fill: true,
                            tension: 0.3,
                            yAxisID: 'y1'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: { display: true, text: 'Units (kWh)', font: { size: 10, weight: 'bold' } }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Bill Cost (NPR)', font: { size: 10, weight: 'bold' } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
