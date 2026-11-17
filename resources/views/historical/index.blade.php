@extends('layouts.app')

@section('title', 'Historical Records')
@section('page_title', 'Historical Rental Records & Archives')

@section('content')
<div class="space-y-6">

    <!-- Context Callout -->
    <div class="bg-indigo-50/70 border border-indigo-200/80 rounded-2xl p-5 shadow-sm">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 flex items-center justify-center shrink-0">
                <i data-lucide="info" class="w-5 h-5"></i>
            </div>
            <div>
                <h4 class="font-bold text-indigo-950 text-sm">Historical Rental Record Keeping</h4>
                <p class="text-xs text-indigo-800/80 mt-1 leading-relaxed">
                    Log historical months from your tenancy even if some utility receipts or exact payments are missing. 
                    Records can be marked as <strong class="text-indigo-950">Verified</strong> (exact receipts available) or <strong class="text-indigo-950">Estimated / Incomplete</strong>. 
                    Per policy, <em class="underline">missing payment amounts are never invented or guessed</em>.
                </p>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Historical Expenses</span>
            <div class="text-xl font-extrabold text-slate-900 mt-1">
                NPR {{ number_format($totalHistoricalExpenses, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Total archived costs</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Historical Paid</span>
            <div class="text-xl font-extrabold text-emerald-600 mt-1">
                NPR {{ number_format($totalHistoricalPaid, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Verified payments logged</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Historical Balance</span>
            <div class="text-xl font-extrabold text-slate-800 mt-1">
                NPR {{ number_format($totalHistoricalBalance, 2) }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Outstanding or unverified</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Verified Records</span>
            <div class="text-xl font-extrabold text-teal-600 mt-1">
                {{ $verifiedCount }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Receipts confirmed</div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-sm">
            <span class="text-xs font-medium text-slate-500 uppercase tracking-wider block">Estimated / Incomplete</span>
            <div class="text-xl font-extrabold text-amber-600 mt-1">
                {{ $estimatedCount }}
            </div>
            <div class="text-xs text-slate-400 mt-1">Needs receipt check</div>
        </div>
    </div>

    <!-- Actions & Filter -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form method="GET" action="{{ route('historical.index') }}" class="flex items-center gap-3">
            <select name="verification" class="text-xs rounded-lg border-slate-300 focus:ring-teal-500 focus:border-teal-500 bg-white px-3 py-2 border">
                <option value="">All Historical Records</option>
                <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>Verified Records Only</option>
                <option value="estimated" {{ request('verification') === 'estimated' ? 'selected' : '' }}>Estimated / Incomplete Only</option>
            </select>
            <button type="submit" class="px-3.5 py-2 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-lg transition">Filter</button>
            @if(request('verification'))
                <a href="{{ route('historical.index') }}" class="text-xs text-rose-600 hover:text-rose-700 font-medium">Reset</a>
            @endif
        </form>

        <a href="{{ route('historical.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-xs font-bold text-white bg-teal-600 hover:bg-teal-700 rounded-xl shadow-sm transition">
            <i data-lucide="plus" class="w-4 h-4"></i>
            <span>Add Historical Month</span>
        </a>
    </div>

    <!-- Historical Records Table -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-200 text-slate-500 uppercase tracking-wider font-semibold">
                        <th class="py-3.5 px-4">Historical Month</th>
                        <th class="py-3.5 px-4">Verification</th>
                        <th class="py-3.5 px-4">Rent</th>
                        <th class="py-3.5 px-4">Electricity</th>
                        <th class="py-3.5 px-4">Water (50%)</th>
                        <th class="py-3.5 px-4">Total Cost</th>
                        <th class="py-3.5 px-4">Known Paid</th>
                        <th class="py-3.5 px-4">Balance</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
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
                            <td class="py-3.5 px-4">
                                @if($rec->is_verified)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <i data-lucide="check-circle" class="w-3 h-3"></i> Verified
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <i data-lucide="help-circle" class="w-3 h-3"></i> Estimated
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-medium text-slate-700">
                                NPR {{ number_format($rec->rent_amount, 2) }}
                            </td>
                            <td class="py-3.5 px-4">
                                @if($rec->electricity_amount > 0)
                                    <span>NPR {{ number_format($rec->electricity_amount, 2) }}</span>
                                    <span class="text-[10px] text-slate-400 block">{{ $rec->electricity_units }} units</span>
                                @else
                                    <span class="text-slate-400 italic">Not recorded</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                @if($rec->water_amount > 0)
                                    <span>NPR {{ number_format($rec->water_amount, 2) }}</span>
                                @else
                                    <span class="text-slate-400 italic">Not recorded</span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 font-bold text-slate-900">
                                NPR {{ number_format($rec->total_expenses, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-600">
                                NPR {{ number_format($rec->total_paid, 2) }}
                            </td>
                            <td class="py-3.5 px-4 font-extrabold {{ $rec->balance > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                                NPR {{ number_format($rec->balance, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('monthly-records.show', $rec) }}" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg">
                                        <i data-lucide="eye" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('monthly-records.edit', $rec) }}" class="p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-lg">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-slate-400">
                                <i data-lucide="history" class="w-10 h-10 mx-auto text-slate-300 mb-2"></i>
                                <p class="font-bold text-slate-700 text-sm">No historical records logged</p>
                                <p class="text-xs text-slate-400 mt-1">Add older months to complete your 2-year rental expense history.</p>
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
