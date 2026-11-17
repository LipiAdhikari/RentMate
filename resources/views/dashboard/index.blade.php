@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Rental Overview & Analytics')

@section('content')
<div class="space-y-6">

    <!-- Outstanding Alert Banner if any unpaid/partial months exist -->
    @if($unpaidRecords->count() > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 sm:p-5 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                        <i data-lucide="alert-circle" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-amber-900 text-sm">Action Needed: {{ $unpaidRecords->count() }} Month(s) Pending Payment</h4>
                        <p class="text-xs text-amber-700 mt-0.5">
                            You have an outstanding balance of <span class="font-bold">NPR {{ number_format($allTimeBalance, 2) }}</span> across pending months.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="openGlobalPaymentModal({{ $unpaidRecords->first()->id }})" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm transition">
                        Pay Latest ({{ $unpaidRecords->first()->short_month }})
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Key Metrics Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <!-- Current/Active Month Expense -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {{ $activeDisplayRecord ? $activeDisplayRecord->short_month : 'Current Month' }} Expenses
                </span>
                <span class="p-2 bg-teal-50 text-teal-600 rounded-xl">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-slate-800">
                    NPR {{ number_format($activeDisplayRecord ? $activeDisplayRecord->total_expenses : 0, 2) }}
                </div>
                <div class="mt-2 text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2">
                    <span>Rent: NPR {{ number_format($activeDisplayRecord ? $activeDisplayRecord->rent_amount : $settings->rent_amount, 0) }}</span>
                    <span>Util: NPR {{ number_format($activeDisplayRecord ? ($activeDisplayRecord->electricity_amount + $activeDisplayRecord->water_amount + $activeDisplayRecord->other_charges) : 0, 0) }}</span>
                </div>
            </div>
        </div>

        <!-- Current Month Balance Due -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">
                    {{ $activeDisplayRecord ? $activeDisplayRecord->short_month : 'Current' }} Balance Due
                </span>
                <span class="p-2 {{ ($activeDisplayRecord && $activeDisplayRecord->balance > 0) ? 'bg-rose-50 text-rose-600' : 'bg-emerald-50 text-emerald-600' }} rounded-xl">
                    <i data-lucide="{{ ($activeDisplayRecord && $activeDisplayRecord->balance > 0) ? 'clock' : 'check-check' }}" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold {{ ($activeDisplayRecord && $activeDisplayRecord->balance > 0) ? 'text-rose-600' : 'text-emerald-600' }}">
                    NPR {{ number_format($activeDisplayRecord ? $activeDisplayRecord->balance : 0, 2) }}
                </div>
                <div class="mt-2 text-xs flex items-center justify-between border-t border-slate-100 pt-2">
                    <span class="text-slate-500">Paid: NPR {{ number_format($activeDisplayRecord ? $activeDisplayRecord->total_paid : 0, 0) }}</span>
                    @if($activeDisplayRecord)
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $activeDisplayRecord->status_badge_class }}">
                            {{ $activeDisplayRecord->status_label }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Total Outstanding Balance All Time -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Outstanding</span>
                <span class="p-2 bg-rose-50 text-rose-600 rounded-xl">
                    <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-slate-800">
                    NPR {{ number_format($allTimeBalance, 2) }}
                </div>
                <div class="mt-2 text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2">
                    <span>Pending Months: {{ $unpaidRecords->count() }}</span>
                    <span class="text-rose-600 font-semibold">{{ $allTimeBalance > 0 ? 'Dues Pending' : 'All Clear' }}</span>
                </div>
            </div>
        </div>

        <!-- Total Expenses Since Moving In -->
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-xs font-medium text-slate-500 uppercase tracking-wider">Total Expenses Since Move-In</span>
                <span class="p-2 bg-indigo-50 text-indigo-600 rounded-xl">
                    <i data-lucide="landmark" class="w-4 h-4"></i>
                </span>
            </div>
            <div class="mt-3">
                <div class="text-2xl font-extrabold text-slate-800">
                    NPR {{ number_format($allTimeExpenses, 2) }}
                </div>
                <div class="mt-2 text-xs text-slate-500 flex items-center justify-between border-t border-slate-100 pt-2">
                    <span>Total Paid: NPR {{ number_format($allTimePaid, 0) }}</span>
                    <span class="text-teal-600 font-medium">Since {{ $settings->rental_start_date ? $settings->rental_start_date->format('M Y') : 'Start' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Breakdown of Active Month (Cards with icons) -->
    @if($activeDisplayRecord)
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-100 gap-3">
                <div>
                    <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                        <span>{{ $activeDisplayRecord->formatted_month }} Detailed Breakdown</span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold border {{ $activeDisplayRecord->status_badge_class }}">
                            {{ $activeDisplayRecord->status_label }}
                        </span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Summary of rent, electricity meter readings, and 50% water share.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('monthly-records.show', $activeDisplayRecord) }}" class="px-3 py-1.5 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                        View Full Details
                    </a>
                    @if($activeDisplayRecord->balance > 0)
                        <button type="button" onclick="openGlobalPaymentModal({{ $activeDisplayRecord->id }})" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm transition">
                            Record Payment
                        </button>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        <i data-lucide="home" class="w-4 h-4 text-slate-400"></i>
                        <span>House Rent</span>
                    </div>
                    <div class="text-lg font-bold text-slate-800">NPR {{ number_format($activeDisplayRecord->rent_amount, 2) }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Fixed monthly rate</div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                        <span>Electricity Bill</span>
                    </div>
                    <div class="text-lg font-bold text-slate-800">NPR {{ number_format($activeDisplayRecord->electricity_amount, 2) }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">
                        {{ $activeDisplayRecord->electricity_units }} units @ NPR {{ $activeDisplayRecord->electricity_rate }}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        <i data-lucide="droplets" class="w-4 h-4 text-sky-500"></i>
                        <span>Water (50% Share)</span>
                    </div>
                    <div class="text-lg font-bold text-slate-800">NPR {{ number_format($activeDisplayRecord->water_amount, 2) }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">
                        Total bill: NPR {{ number_format($activeDisplayRecord->total_water_bill, 0) }}
                    </div>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100">
                    <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                        <i data-lucide="layers" class="w-4 h-4 text-purple-500"></i>
                        <span>Other Charges</span>
                    </div>
                    <div class="text-lg font-bold text-slate-800">NPR {{ number_format($activeDisplayRecord->other_charges, 2) }}</div>
                    <div class="text-[11px] text-slate-400 mt-1">Waste / repairs / etc.</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Monthly Expense Overview Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Monthly Rental Expense Overview</h3>
                    <p class="text-xs text-slate-500">Cost trends across recent months</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg">NPR</span>
            </div>
            <div class="h-64">
                <canvas id="monthlyExpenseChart"></canvas>
            </div>
        </div>

        <!-- Electricity Consumption Chart -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Electricity Consumption Trend</h3>
                    <p class="text-xs text-slate-500">Units consumed (kWh) per month</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg">Units (kWh)</span>
            </div>
            <div class="h-64">
                <canvas id="electricityUnitsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Two Column Table Section: Pending Months & Recent Payments -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Unpaid & Partially Paid Months -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                        <i data-lucide="alert-circle" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Pending & Partial Dues</h4>
                        <p class="text-[11px] text-slate-500">Months requiring payment action</p>
                    </div>
                </div>
                <span class="text-xs font-bold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full border border-rose-100">
                    {{ $unpaidRecords->count() }} Months
                </span>
            </div>

            <div class="divide-y divide-slate-100 flex-1 overflow-x-auto">
                @forelse($unpaidRecords as $rec)
                    <div class="p-4 hover:bg-slate-50 flex items-center justify-between gap-4 transition">
                        <div>
                            <div class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                <a href="{{ route('monthly-records.show', $rec) }}" class="hover:text-teal-600 transition">
                                    {{ $rec->formatted_month }}
                                </a>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold border {{ $rec->status_badge_class }}">
                                    {{ $rec->status_label }}
                                </span>
                            </div>
                            <div class="text-xs text-slate-400 mt-1">
                                Total: NPR {{ number_format($rec->total_expenses, 2) }} | Paid: NPR {{ number_format($rec->total_paid, 2) }}
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <div class="text-sm font-extrabold text-rose-600">NPR {{ number_format($rec->balance, 2) }}</div>
                            <button type="button" onclick="openGlobalPaymentModal({{ $rec->id }})" class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-teal-600 hover:text-teal-700 bg-teal-50 px-2 py-0.5 rounded hover:bg-teal-100 transition">
                                <i data-lucide="plus" class="w-3 h-3"></i> Pay
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <i data-lucide="check-circle" class="w-8 h-8 text-emerald-500 mx-auto mb-2"></i>
                        <span>Awesome! All recorded months are fully settled.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Payment Transactions -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-teal-50 text-teal-600 flex items-center justify-center">
                        <i data-lucide="arrow-down-left" class="w-4 h-4"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">Recent Transactions</h4>
                        <p class="text-[11px] text-slate-500">Latest recorded payments</p>
                    </div>
                </div>
                <a href="{{ route('payments.index') }}" class="text-xs text-teal-600 hover:text-teal-700 font-semibold">View All</a>
            </div>

            <div class="divide-y divide-slate-100 flex-1 overflow-x-auto">
                @forelse($recentPayments as $pay)
                    <div class="p-4 hover:bg-slate-50 flex items-center justify-between gap-4 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold shrink-0 border {{ $pay->method_badge_class }}">
                                <i data-lucide="check" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="font-bold text-slate-800 text-sm">
                                    {{ $pay->monthlyRecord ? $pay->monthlyRecord->short_month : 'Rent Payment' }}
                                </div>
                                <div class="text-xs text-slate-400 flex items-center gap-2 mt-0.5">
                                    <span>{{ $pay->payment_date->format('M d, Y') }}</span>
                                    <span>•</span>
                                    <span class="font-medium text-slate-600">{{ $pay->payment_method }}</span>
                                    @if($pay->transaction_reference)
                                        <span class="text-[10px] text-slate-400 truncate max-w-[120px]">({{ $pay->transaction_reference }})</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <span class="text-sm font-extrabold text-emerald-600">
                                +NPR {{ number_format($pay->amount, 2) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-400 text-xs">
                        <i data-lucide="receipt" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                        <span>No payments recorded yet.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const labels = @json($chartLabels);
        const rentData = @json($chartRent);
        const elecData = @json($chartElectricity);
        const waterData = @json($chartWater);
        const totalData = @json($chartTotal);
        const unitsData = @json($chartUnits);

        // Monthly Expense Chart
        const ctxExpense = document.getElementById('monthlyExpenseChart');
        if (ctxExpense) {
            new Chart(ctxExpense, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Rent',
                            data: rentData,
                            backgroundColor: '#0d9488', // teal
                            borderRadius: 4,
                            stack: 'expenses'
                        },
                        {
                            label: 'Electricity',
                            data: elecData,
                            backgroundColor: '#f59e0b', // amber
                            borderRadius: 4,
                            stack: 'expenses'
                        },
                        {
                            label: 'Water (50%)',
                            data: waterData,
                            backgroundColor: '#0284c7', // sky
                            borderRadius: 4,
                            stack: 'expenses'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': NPR ' + Number(context.raw).toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            stacked: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(value) { return 'NPR ' + (value >= 1000 ? (value/1000) + 'k' : value); },
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }

        // Electricity Units Chart
        const ctxUnits = document.getElementById('electricityUnitsChart');
        if (ctxUnits) {
            new Chart(ctxUnits, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Units Consumed (kWh)',
                        data: unitsData,
                        borderColor: '#f59e0b',
                        backgroundColor: 'rgba(245, 158, 11, 0.1)',
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#f59e0b',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) { return context.raw + ' units (kWh)'; }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                        y: {
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(value) { return value + ' units'; },
                                font: { size: 11 }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
