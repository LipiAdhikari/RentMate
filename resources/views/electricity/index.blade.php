@extends('layouts.app')

@section('title', 'Electricity Tracker')
@section('page_title', 'Electricity Consumption & Meter Tracker')

@section('content')
<div class="space-y-6">

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Electricity Cost</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">
                NPR {{ number_format($totalCost, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Across all recorded months</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Units Consumed</span>
            <div class="text-2xl font-extrabold text-amber-600 mt-1">
                {{ number_format($totalUnits, 1) }} kWh
            </div>
            <div class="text-xs text-slate-400 mt-1">Cumulative meter usage</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Average Monthly Units</span>
            <div class="text-2xl font-extrabold text-slate-800 mt-1">
                {{ number_format($avgUnits, 1) }} kWh
            </div>
            <div class="text-xs text-slate-400 mt-1">Avg cost: NPR {{ number_format($avgCost, 2) }}</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Current Price Rate</span>
            <div class="text-2xl font-extrabold text-teal-600 mt-1">
                NPR {{ number_format($settings->electricity_rate, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Per unit / kWh</div>
        </div>
    </div>

    <!-- Electricity Trends Chart -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Monthly Electricity Consumption & Costs</h3>
                <p class="text-xs text-slate-500">Historical trend of units consumed and total electricity bill</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-semibold bg-amber-50 text-amber-700 rounded-lg">Units & Cost</span>
        </div>
        <div class="h-64">
            <canvas id="elecFullChart"></canvas>
        </div>
    </div>

    <!-- Meter Readings Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="zap" class="w-4 h-4 text-amber-500"></i>
                    <span>Meter Reading Log</span>
                </h3>
                <p class="text-xs text-slate-500">Units = Current Reading - Previous Reading</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Month / Year</th>
                        <th class="py-3 px-4">Prev Reading</th>
                        <th class="py-3 px-4">Curr Reading</th>
                        <th class="py-3 px-4">Units Consumed</th>
                        <th class="py-3 px-4">Unit Rate</th>
                        <th class="py-3 px-4">Total Cost</th>
                        <th class="py-3 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="py-3.5 px-4 font-bold text-slate-800">
                                <a href="{{ route('monthly-records.show', $rec) }}" class="hover:text-teal-600 transition">
                                    {{ $rec->formatted_month }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">
                                {{ number_format($rec->prev_electricity_reading, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                {{ number_format($rec->curr_electricity_reading, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded font-bold text-xs bg-amber-50 text-amber-700 border border-amber-200">
                                    {{ $rec->electricity_units }} kWh
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                NPR {{ number_format($rec->electricity_rate, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold text-slate-900 text-sm">
                                NPR {{ number_format($rec->electricity_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" onclick="openUpdateModal({{ $rec->id }}, '{{ $rec->formatted_month }}', {{ $rec->prev_electricity_reading }}, {{ $rec->curr_electricity_reading }}, {{ $rec->electricity_rate }})" class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                    Update Readings
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-slate-400">
                                <span>No electricity meter logs found.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Quick Update Electricity Modal -->
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
                <input type="number" step="0.01" min="0" name="prev_electricity_reading" id="modal_prev" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Current Meter Reading *</label>
                <input type="number" step="0.01" min="0" name="curr_electricity_reading" id="modal_curr" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Electricity Rate (NPR/unit) *</label>
                <input type="number" step="0.01" min="0" name="electricity_rate" id="modal_rate" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border">
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
                            borderRadius: 4,
                            yAxisID: 'y'
                        },
                        {
                            type: 'line',
                            label: 'Electricity Bill (NPR)',
                            data: costs,
                            borderColor: '#0d9488',
                            backgroundColor: 'transparent',
                            borderWidth: 2,
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
                            title: { display: true, text: 'Units (kWh)', font: { size: 10 } }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'Bill (NPR)', font: { size: 10 } }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
