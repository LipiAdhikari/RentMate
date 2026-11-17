@extends('layouts.app')

@section('title', 'Water Bills')
@section('page_title', 'Water Bills Tracker (50/50 Share)')

@section('content')
<div class="space-y-6">

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Total Water Invoiced</span>
            <div class="text-2xl font-extrabold text-slate-900 mt-1">
                NPR {{ number_format($totalWaterBills, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">From Kathmandu / Local supply</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Your Cumulative Share</span>
            <div class="text-2xl font-extrabold text-sky-600 mt-1">
                NPR {{ number_format($tenantTotalShare, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Based on configured 50% split</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Owner's Share</span>
            <div class="text-2xl font-extrabold text-slate-700 mt-1">
                NPR {{ number_format($ownerTotalShare, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Paid directly by landlord</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Avg. Monthly Water Cost</span>
            <div class="text-2xl font-extrabold text-teal-600 mt-1">
                NPR {{ number_format($avgTenantShare, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Tenant portion per month</div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-sm">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Monthly Water Expense Split (Tenant vs Owner)</h3>
                <p class="text-xs text-slate-500">Historical comparison of divided water utility bills</p>
            </div>
            <span class="px-2.5 py-1 text-xs font-semibold bg-sky-50 text-sky-700 rounded-lg">50 / 50 Ratio</span>
        </div>
        <div class="h-64">
            <canvas id="waterChart"></canvas>
        </div>
    </div>

    <!-- Water Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/60">
            <div>
                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                    <i data-lucide="droplets" class="w-4 h-4 text-sky-500"></i>
                    <span>Water Bill History</span>
                </h3>
                <p class="text-xs text-slate-500">Tenant Share = Total Bill × Share% / 100</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3 px-4">Month / Year</th>
                        <th class="py-3 px-4">Total Authority Bill</th>
                        <th class="py-3 px-4">Sharing %</th>
                        <th class="py-3 px-4">Your Share (Tenant)</th>
                        <th class="py-3 px-4">Owner's Share</th>
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
                            <td class="py-3.5 px-4 font-mono font-medium text-slate-700">
                                NPR {{ number_format($rec->total_water_bill, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded font-bold text-xs bg-sky-50 text-sky-700 border border-sky-200">
                                    {{ $rec->tenant_water_percentage }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-sky-700 text-sm">
                                NPR {{ number_format($rec->water_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                NPR {{ number_format($rec->owner_water_share, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button type="button" onclick="openWaterModal({{ $rec->id }}, '{{ $rec->formatted_month }}', {{ $rec->total_water_bill }}, {{ $rec->tenant_water_percentage }})" class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                                    Update Bill
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">
                                <span>No water records logged yet.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Quick Update Water Modal -->
<div id="water-modal" class="fixed inset-0 z-50 bg-slate-900/50 backdrop-blur-sm hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i data-lucide="droplets" class="w-4 h-4 text-sky-500"></i>
                <span id="water-modal-month">Update Water Bill</span>
            </h3>
            <button type="button" onclick="closeWaterModal()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>

        <form id="water-modal-form" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Total Water Bill from Authority (NPR) *</label>
                <input type="number" step="0.01" min="0" name="total_water_bill" id="modal_total_water" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tenant Percentage (%) *</label>
                <input type="number" step="0.01" min="0" max="100" name="tenant_water_percentage" id="modal_pct" required class="w-full text-xs rounded-lg border-slate-300 px-3 py-2 border">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeWaterModal()" class="px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-100 rounded-lg">Cancel</button>
                <button type="submit" class="px-4 py-1.5 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-lg shadow-sm">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openWaterModal(id, month, total, pct) {
        const modal = document.getElementById('water-modal');
        const form = document.getElementById('water-modal-form');
        form.action = `/water-bills/${id}`;
        document.getElementById('water-modal-month').innerText = 'Update Water for ' + month;
        document.getElementById('modal_total_water').value = total;
        document.getElementById('modal_pct').value = pct;
        modal.classList.remove('hidden');
        if (window.lucide) window.lucide.createIcons();
    }

    function closeWaterModal() {
        document.getElementById('water-modal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        const labels = @json($chartLabels);
        const tenant = @json($chartTenantShares);
        const owner = @json($chartOwnerShares);

        const ctx = document.getElementById('waterChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Your Share (Tenant)',
                            data: tenant,
                            backgroundColor: '#0284c7', // sky
                            borderRadius: 4,
                            stack: 'water'
                        },
                        {
                            label: "Owner's Share",
                            data: owner,
                            backgroundColor: '#94a3b8', // slate
                            borderRadius: 4,
                            stack: 'water'
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
                                label: function(context) { return context.dataset.label + ': NPR ' + Number(context.raw).toLocaleString(); }
                            }
                        }
                    },
                    scales: {
                        x: { grid: { display: false } },
                        y: {
                            stacked: true,
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(val) { return 'NPR ' + val; }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
