@extends('layouts.app')

@section('title', 'Reports & Analytics')
@section('page_title', 'Expense Reports & Analytics')

@section('content')
<div class="space-y-6">

    <!-- Filters and Export Actions Card -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Date Filters -->
        <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3 text-xs">
            <div>
                <select name="year" class="rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                    <option value="">All Years</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input type="date" name="from_date" value="{{ request('from_date') }}" placeholder="From date" class="rounded-lg border-slate-300 px-3 py-1.5 border">
                <span class="text-slate-400">to</span>
                <input type="date" name="to_date" value="{{ request('to_date') }}" placeholder="To date" class="rounded-lg border-slate-300 px-3 py-1.5 border">
            </div>

            <button type="submit" class="px-3.5 py-2 font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                Filter
            </button>

            @if(request()->hasAny(['year', 'from_date', 'to_date']))
                <a href="{{ route('reports.index') }}" class="text-rose-600 hover:text-rose-700 font-medium">Reset</a>
            @endif
        </form>

        <!-- Export Buttons -->
        <div class="flex items-center gap-2.5">
            <a href="{{ route('reports.export.csv', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 border border-slate-300 rounded-xl shadow-sm transition">
                <i data-lucide="file-spreadsheet" class="w-4 h-4 text-emerald-600"></i>
                <span>Export CSV / Excel</span>
            </a>
            <a href="{{ route('reports.export.pdf', request()->query()) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-xl shadow-sm transition">
                <i data-lucide="file-text" class="w-4 h-4 text-teal-400"></i>
                <span>Download PDF Statement</span>
            </a>
        </div>
    </div>

    <!-- Aggregates Metrics Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3 text-xs">
        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] text-slate-400 uppercase font-medium block">Total Expenses</span>
            <div class="text-lg font-bold text-slate-900 mt-1">NPR {{ number_format($totalExpenses, 2) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] text-slate-400 uppercase font-medium block">Total Rent</span>
            <div class="text-lg font-bold text-teal-700 mt-1">NPR {{ number_format($totalRent, 2) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] text-slate-400 uppercase font-medium block">Electricity Total</span>
            <div class="text-lg font-bold text-amber-600 mt-1">NPR {{ number_format($totalElectricity, 2) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] text-slate-400 uppercase font-medium block">Water Total (50%)</span>
            <div class="text-lg font-bold text-sky-600 mt-1">NPR {{ number_format($totalWater, 2) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] text-slate-400 uppercase font-medium block">Other Charges</span>
            <div class="text-lg font-bold text-purple-600 mt-1">NPR {{ number_format($totalOther, 2) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] text-slate-400 uppercase font-medium block">Payments Made</span>
            <div class="text-lg font-bold text-emerald-600 mt-1">NPR {{ number_format($totalPaid, 2) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-slate-200/80 shadow-sm">
            <span class="text-[11px] text-slate-400 uppercase font-medium block">Total Balance Due</span>
            <div class="text-lg font-bold {{ $totalBalance > 0 ? 'text-rose-600' : 'text-emerald-600' }} mt-1">
                NPR {{ number_format($totalBalance, 2) }}
            </div>
        </div>
    </div>

    <!-- Analytics Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Expenses Breakdown Doughnut -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm flex flex-col">
            <div class="mb-4">
                <h3 class="font-bold text-slate-800 text-sm">Expense Composition</h3>
                <p class="text-xs text-slate-500">Distribution across rent and utilities</p>
            </div>
            <div class="relative flex-1 flex items-center justify-center min-h-[220px]">
                <canvas id="categoryDoughnutChart"></canvas>
            </div>
        </div>

        <!-- Monthly Trends Comparison (Expenses vs Paid) -->
        <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm lg:col-span-2 flex flex-col">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Expenses vs. Payments Over Time</h3>
                    <p class="text-xs text-slate-500">Compare monthly billed expenses with actual payments made</p>
                </div>
                <span class="px-2.5 py-1 text-xs font-semibold bg-teal-50 text-teal-700 rounded-lg">Cashflow</span>
            </div>
            <div class="h-64">
                <canvas id="trendsComparisonChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Detailed Report Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Filtered Rental Statements</h3>
                <p class="text-xs text-slate-500">Showing {{ $records->count() }} records for selected period</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Period</th>
                        <th class="py-3 px-4 text-right">Rent</th>
                        <th class="py-3 px-4 text-right">Electricity</th>
                        <th class="py-3 px-4 text-right">Water (50%)</th>
                        <th class="py-3 px-4 text-right">Other</th>
                        <th class="py-3 px-4 text-right">Total Expenses</th>
                        <th class="py-3 px-4 text-right">Total Paid</th>
                        <th class="py-3 px-4 text-right">Remaining Balance</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $r)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                <a href="{{ route('monthly-records.show', $r) }}" class="hover:text-teal-600">
                                    {{ $r->formatted_month }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 text-right font-medium text-slate-700">
                                NPR {{ number_format($r->rent_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                NPR {{ number_format($r->electricity_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                NPR {{ number_format($r->water_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right text-slate-500">
                                NPR {{ number_format($r->other_charges, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-slate-900">
                                NPR {{ number_format($r->total_expenses, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-semibold text-emerald-600">
                                NPR {{ number_format($r->total_paid, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-extrabold {{ $r->balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                NPR {{ number_format($r->balance, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $r->status_badge_class }}">
                                    {{ $r->status_label }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-8 text-center text-slate-400">
                                <span>No records matching filter criteria.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 font-bold text-slate-900 border-t-2 border-slate-300">
                        <td class="py-3 px-4">TOTALS</td>
                        <td class="py-3 px-4 text-right">NPR {{ number_format($totalRent, 2) }}</td>
                        <td class="py-3 px-4 text-right">NPR {{ number_format($totalElectricity, 2) }}</td>
                        <td class="py-3 px-4 text-right">NPR {{ number_format($totalWater, 2) }}</td>
                        <td class="py-3 px-4 text-right">NPR {{ number_format($totalOther, 2) }}</td>
                        <td class="py-3 px-4 text-right text-teal-800">NPR {{ number_format($totalExpenses, 2) }}</td>
                        <td class="py-3 px-4 text-right text-emerald-700">NPR {{ number_format($totalPaid, 2) }}</td>
                        <td class="py-3 px-4 text-right {{ $totalBalance > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                            NPR {{ number_format($totalBalance, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Doughnut chart
        const doughnutCtx = document.getElementById('categoryDoughnutChart');
        if (doughnutCtx) {
            new Chart(doughnutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['House Rent', 'Electricity', 'Water (50%)', 'Other Charges'],
                    datasets: [{
                        data: [{{ $totalRent }}, {{ $totalElectricity }}, {{ $totalWater }}, {{ $totalOther }}],
                        backgroundColor: ['#0d9488', '#f59e0b', '#0284c7', '#a855f7'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': NPR ' + Number(context.raw).toLocaleString();
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }

        // Trends Bar Chart
        const trendsCtx = document.getElementById('trendsComparisonChart');
        if (trendsCtx) {
            const labels = @json($chartLabels);
            const totalData = @json($chartTotal);
            const paidData = @json($chartPaid);

            new Chart(trendsCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Billed Expenses',
                            data: totalData,
                            backgroundColor: '#94a3b8',
                            borderRadius: 4
                        },
                        {
                            label: 'Payments Made',
                            data: paidData,
                            backgroundColor: '#0d9488',
                            borderRadius: 4
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
                                label: function(c) { return c.dataset.label + ': NPR ' + Number(c.raw).toLocaleString(); }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(val) { return 'NPR ' + (val >= 1000 ? (val/1000) + 'k' : val); }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
