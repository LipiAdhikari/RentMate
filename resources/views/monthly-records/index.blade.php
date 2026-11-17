@extends('layouts.app')

@section('title', 'Monthly Records')
@section('page_title', 'Monthly Rental Records')

@section('content')
<div class="space-y-6">

    <!-- Header Actions and Filters -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <!-- Filter Form -->
        <form method="GET" action="{{ route('monthly-records.index') }}" class="flex flex-wrap items-center gap-3">
            <div>
                <select name="year" class="text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                    <option value="">All Years</option>
                    @foreach($availableYears as $yr)
                        <option value="{{ $yr }}" {{ request('year') == $yr ? 'selected' : '' }}>Year {{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <select name="status" class="text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                    <option value="">All Payment Statuses</option>
                    <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                    <option value="partially_paid" {{ request('status') === 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Fully Paid</option>
                    <option value="overpaid" {{ request('status') === 'overpaid' ? 'selected' : '' }}>Overpaid</option>
                </select>
            </div>

            <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">
                Filter
            </button>

            @if(request()->hasAny(['year', 'status']))
                <a href="{{ route('monthly-records.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-medium">
                    Clear Filters
                </a>
            @endif
        </form>

        <!-- Create Button -->
        <div class="flex items-center gap-2">
            <a href="{{ route('monthly-records.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Monthly Record</span>
            </a>
        </div>
    </div>

    <!-- Monthly Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 font-semibold uppercase tracking-wider">
                        <th class="py-3.5 px-4">Month / Year</th>
                        <th class="py-3.5 px-4">Rent</th>
                        <th class="py-3.5 px-4">Electricity (Units)</th>
                        <th class="py-3.5 px-4">Water (50%)</th>
                        <th class="py-3.5 px-4">Other</th>
                        <th class="py-3.5 px-4">Total Expenses</th>
                        <th class="py-3.5 px-4">Total Paid</th>
                        <th class="py-3.5 px-4">Balance Due</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($records as $rec)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-4 px-4 font-bold text-slate-800">
                                <a href="{{ route('monthly-records.show', $rec) }}" class="hover:text-teal-600 transition flex items-center gap-1.5">
                                    <span>{{ $rec->formatted_month }}</span>
                                    @if($rec->is_historical)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] bg-slate-100 text-slate-500 border border-slate-200 font-normal">Historical</span>
                                    @endif
                                </a>
                                <span class="text-[10px] text-slate-400 block font-normal">{{ $rec->record_date->format('M 01, Y') }}</span>
                            </td>
                            <td class="py-4 px-4 font-medium text-slate-700">
                                NPR {{ number_format($rec->rent_amount, 2) }}
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-800">NPR {{ number_format($rec->electricity_amount, 2) }}</div>
                                <div class="text-[10px] text-slate-400">{{ $rec->electricity_units }} units ({{ $rec->prev_electricity_reading }} → {{ $rec->curr_electricity_reading }})</div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-800">NPR {{ number_format($rec->water_amount, 2) }}</div>
                                <div class="text-[10px] text-slate-400">Total: NPR {{ number_format($rec->total_water_bill, 0) }}</div>
                            </td>
                            <td class="py-4 px-4 text-slate-600">
                                NPR {{ number_format($rec->other_charges, 2) }}
                            </td>
                            <td class="py-4 px-4 font-bold text-slate-900">
                                NPR {{ number_format($rec->total_expenses, 2) }}
                            </td>
                            <td class="py-4 px-4 font-semibold text-emerald-600">
                                NPR {{ number_format($rec->total_paid, 2) }}
                            </td>
                            <td class="py-4 px-4 font-extrabold {{ $rec->balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                NPR {{ number_format($rec->balance, 2) }}
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold border {{ $rec->status_badge_class }}">
                                    {{ $rec->status_label }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" onclick="openGlobalPaymentModal({{ $rec->id }})" title="Record Payment" class="p-1.5 rounded-lg text-teal-600 hover:bg-teal-50 transition">
                                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                                    </button>
                                    <a href="{{ route('monthly-records.show', $rec) }}" title="View Record" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('monthly-records.edit', $rec) }}" title="Edit Record" class="p-1.5 rounded-lg text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                    <button type="button" onclick="confirmDelete('{{ route('monthly-records.destroy', $rec) }}', 'Delete record for {{ $rec->formatted_month }}? All linked payments will also be deleted.')" title="Delete Record" class="p-1.5 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-50 transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 px-4 text-center">
                                <div class="max-w-xs mx-auto text-slate-400">
                                    <i data-lucide="calendar-x" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                    <p class="font-bold text-slate-700 text-sm">No monthly records found</p>
                                    <p class="text-xs text-slate-400 mt-1">Get started by creating your first monthly rental record.</p>
                                    <a href="{{ route('monthly-records.create') }}" class="mt-4 inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-teal-600 rounded-lg hover:bg-teal-700 transition">
                                        <i data-lucide="plus" class="w-4 h-4"></i> Create Record
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->hasPages())
            <div class="p-4 border-t border-slate-200">
                {{ $records->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
