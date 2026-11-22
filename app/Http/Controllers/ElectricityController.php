<?php

namespace App\Http\Controllers;

use App\Models\ElectricityBill;
use App\Models\ElectricityBillAllocation;
use App\Models\ElectricityPayment;
use App\Models\MonthlyRecord;
use App\Models\RentalSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ElectricityController extends Controller
{
    /**
     * Display flexible electricity bills dashboard and meter reading history.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $settings = RentalSetting::getActiveForUser($userId);

        // Flexible Electricity Bills Query
        $billsQuery = ElectricityBill::forUser($userId)
            ->with(['allocations.monthlyRecord', 'payments'])
            ->latestFirst();

        // Filters
        if ($request->filled('status') && $request->status !== 'all') {
            $billsQuery->byStatus($request->status);
        }

        if ($request->filled('from_date')) {
            $billsQuery->where('billing_period_start', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $billsQuery->where('billing_period_end', '<=', $request->to_date);
        }

        $bills = $billsQuery->get();

        // Summary KPI Metrics
        $allBills = ElectricityBill::forUser($userId)->get();
        $totalUnits = (float) $allBills->sum('units_consumed');
        $totalCost = (float) $allBills->sum('total_amount');
        $totalPaid = (float) $allBills->sum('paid_amount');
        $totalBalance = (float) $allBills->sum('balance');
        $unpaidCount = $allBills->where('status', 'unpaid')->count();
        $partiallyPaidCount = $allBills->where('status', 'partially_paid')->count();
        $paidCount = $allBills->where('status', 'paid')->count();

        $avgUnits = $allBills->count() > 0 ? round($totalUnits / $allBills->count(), 1) : 0;
        $avgCost = $allBills->count() > 0 ? round($totalCost / $allBills->count(), 2) : 0;

        // Chart data chronologically
        $chartBills = ElectricityBill::forUser($userId)->chronological()->get();
        $chartLabels = [];
        $chartUnits = [];
        $chartCosts = [];

        foreach ($chartBills as $bill) {
            $chartLabels[] = $bill->formatted_period;
            $chartUnits[] = (float) $bill->units_consumed;
            $chartCosts[] = (float) $bill->total_amount;
        }

        // Monthly records for multi-month allocation options
        $monthlyRecords = MonthlyRecord::forUser($userId)->chronological()->get();

        // Legacy monthly records log for backward compatibility
        $records = MonthlyRecord::forUser($userId)->orderBy('record_date', 'desc')->get();
        $latestRecord = $records->first();

        return view('electricity.index', compact(
            'bills',
            'records',
            'monthlyRecords',
            'settings',
            'totalUnits',
            'totalCost',
            'totalPaid',
            'totalBalance',
            'unpaidCount',
            'partiallyPaidCount',
            'paidCount',
            'avgUnits',
            'avgCost',
            'latestRecord',
            'chartLabels',
            'chartUnits',
            'chartCosts'
        ));
    }

    /**
     * Store a newly created flexible electricity bill.
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'billing_period_start' => ['required', 'date'],
            'billing_period_end' => ['required', 'date', 'after_or_equal:billing_period_start'],
            'previous_meter_reading' => ['required', 'numeric', 'min:0'],
            'current_meter_reading' => ['required', 'numeric', 'gte:previous_meter_reading'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'is_historical' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.monthly_record_id' => ['required_with:allocations', 'exists:monthly_records,id'],
            'allocations.*.allocated_amount' => ['required_with:allocations', 'numeric', 'min:0'],
        ], [
            'current_meter_reading.gte' => 'Current meter reading cannot be lower than previous meter reading.',
            'billing_period_end.after_or_equal' => 'Billing period end date must be on or after the start date.',
        ]);

        // Duplicate bill prevention
        $duplicate = ElectricityBill::forUser($userId)
            ->whereDate('billing_period_start', $validated['billing_period_start'])
            ->whereDate('billing_period_end', $validated['billing_period_end'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'billing_period_start' => 'An electricity bill for this exact billing period already exists.'
            ]);
        }

        $units = round((float)$validated['current_meter_reading'] - (float)$validated['previous_meter_reading'], 2);
        $totalAmount = round($units * (float)$validated['rate_per_unit'], 2);

        // Validate allocations sum does not exceed total amount
        $allocationsData = $validated['allocations'] ?? [];
        $totalAllocated = 0.00;
        foreach ($allocationsData as $item) {
            $totalAllocated += (float)($item['allocated_amount'] ?? 0);
        }
        $totalAllocated = round($totalAllocated, 2);

        if ($totalAllocated > $totalAmount) {
            return back()->withInput()->withErrors([
                'allocations' => sprintf('Total allocated amount (NPR %s) exceeds the total electricity bill (NPR %s).', number_format($totalAllocated, 2), number_format($totalAmount, 2))
            ]);
        }

        DB::transaction(function () use ($userId, $validated, $units, $totalAmount, $allocationsData) {
            $bill = ElectricityBill::create([
                'user_id' => $userId,
                'billing_period_start' => $validated['billing_period_start'],
                'billing_period_end' => $validated['billing_period_end'],
                'previous_meter_reading' => $validated['previous_meter_reading'],
                'current_meter_reading' => $validated['current_meter_reading'],
                'units_consumed' => $units,
                'rate_per_unit' => $validated['rate_per_unit'],
                'total_amount' => $totalAmount,
                'paid_amount' => 0.00,
                'balance' => $totalAmount,
                'status' => 'unpaid',
                'is_historical' => !empty($validated['is_historical']),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Save allocations if provided
            foreach ($allocationsData as $alloc) {
                $amount = (float)($alloc['allocated_amount'] ?? 0);
                if ($amount > 0 && !empty($alloc['monthly_record_id'])) {
                    // Check ownership of monthly record
                    $record = MonthlyRecord::where('id', $alloc['monthly_record_id'])
                        ->where('user_id', $userId)
                        ->first();

                    if ($record) {
                        ElectricityBillAllocation::create([
                            'electricity_bill_id' => $bill->id,
                            'monthly_record_id' => $record->id,
                            'allocated_amount' => $amount,
                            'notes' => 'Allocated from bill #' . $bill->id,
                        ]);
                        $record->recalculate(true);
                    }
                }
            }
        });

        return redirect()->route('electricity.index')->with('success', 'Electricity bill created successfully.');
    }

    /**
     * Update an existing electricity bill.
     */
    public function update(Request $request, ElectricityBill $electricityBill)
    {
        if ($electricityBill->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $userId = auth()->id();

        $validated = $request->validate([
            'billing_period_start' => ['required', 'date'],
            'billing_period_end' => ['required', 'date', 'after_or_equal:billing_period_start'],
            'previous_meter_reading' => ['required', 'numeric', 'min:0'],
            'current_meter_reading' => ['required', 'numeric', 'gte:previous_meter_reading'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'is_historical' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'allocations' => ['nullable', 'array'],
            'allocations.*.monthly_record_id' => ['required_with:allocations', 'exists:monthly_records,id'],
            'allocations.*.allocated_amount' => ['required_with:allocations', 'numeric', 'min:0'],
        ], [
            'current_meter_reading.gte' => 'Current meter reading cannot be lower than previous meter reading.',
            'billing_period_end.after_or_equal' => 'Billing period end date must be on or after the start date.',
        ]);

        // Duplicate check (ignoring current bill)
        $duplicate = ElectricityBill::forUser($userId)
            ->where('id', '!=', $electricityBill->id)
            ->whereDate('billing_period_start', $validated['billing_period_start'])
            ->whereDate('billing_period_end', $validated['billing_period_end'])
            ->exists();

        if ($duplicate) {
            return back()->withInput()->withErrors([
                'billing_period_start' => 'Another electricity bill for this exact billing period already exists.'
            ]);
        }

        $units = round((float)$validated['current_meter_reading'] - (float)$validated['previous_meter_reading'], 2);
        $totalAmount = round($units * (float)$validated['rate_per_unit'], 2);

        $allocationsData = $validated['allocations'] ?? [];
        $totalAllocated = 0.00;
        foreach ($allocationsData as $item) {
            $totalAllocated += (float)($item['allocated_amount'] ?? 0);
        }
        $totalAllocated = round($totalAllocated, 2);

        if ($totalAllocated > $totalAmount) {
            return back()->withInput()->withErrors([
                'allocations' => sprintf('Total allocated amount (NPR %s) exceeds the total electricity bill (NPR %s).', number_format($totalAllocated, 2), number_format($totalAmount, 2))
            ]);
        }

        DB::transaction(function () use ($electricityBill, $userId, $validated, $units, $totalAmount, $allocationsData) {
            $electricityBill->update([
                'billing_period_start' => $validated['billing_period_start'],
                'billing_period_end' => $validated['billing_period_end'],
                'previous_meter_reading' => $validated['previous_meter_reading'],
                'current_meter_reading' => $validated['current_meter_reading'],
                'units_consumed' => $units,
                'rate_per_unit' => $validated['rate_per_unit'],
                'total_amount' => $totalAmount,
                'is_historical' => !empty($validated['is_historical']),
                'notes' => $validated['notes'] ?? null,
            ]);

            // Sync allocations
            $oldRecords = $electricityBill->monthlyRecords()->get();
            $electricityBill->allocations()->delete();

            foreach ($allocationsData as $alloc) {
                $amount = (float)($alloc['allocated_amount'] ?? 0);
                if ($amount > 0 && !empty($alloc['monthly_record_id'])) {
                    $record = MonthlyRecord::where('id', $alloc['monthly_record_id'])
                        ->where('user_id', $userId)
                        ->first();

                    if ($record) {
                        ElectricityBillAllocation::create([
                            'electricity_bill_id' => $electricityBill->id,
                            'monthly_record_id' => $record->id,
                            'allocated_amount' => $amount,
                            'notes' => 'Allocated from bill #' . $electricityBill->id,
                        ]);
                        $record->recalculate(true);
                    }
                }
            }

            foreach ($oldRecords as $oldRec) {
                $oldRec->recalculate(true);
            }

            $electricityBill->recalculate(true);
        });

        return redirect()->route('electricity.index')->with('success', 'Electricity bill updated successfully.');
    }

    /**
     * Delete an electricity bill.
     */
    public function destroy(ElectricityBill $electricityBill)
    {
        if ($electricityBill->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($electricityBill) {
            $affectedRecords = $electricityBill->monthlyRecords()->get();
            $electricityBill->delete();

            foreach ($affectedRecords as $rec) {
                $rec->recalculate(true);
            }
        });

        return redirect()->route('electricity.index')->with('success', 'Electricity bill deleted successfully.');
    }

    /**
     * Record a payment against an electricity bill.
     */
    public function storePayment(Request $request, ElectricityBill $electricityBill)
    {
        if ($electricityBill->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'payment_method' => ['required', 'string', 'max:50'],
            'transaction_reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($electricityBill, $validated) {
            ElectricityPayment::create([
                'electricity_bill_id' => $electricityBill->id,
                'user_id' => auth()->id(),
                'payment_date' => $validated['payment_date'],
                'amount' => $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $electricityBill->recalculate(true);
        });

        return redirect()->route('electricity.index')->with('success', sprintf('Payment of NPR %s recorded for electricity bill (%s).', number_format($validated['amount'], 2), $electricityBill->formatted_period));
    }

    /**
     * Delete an electricity payment.
     */
    public function destroyPayment(ElectricityPayment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $bill = $payment->bill;
        $amount = $payment->amount;

        DB::transaction(function () use ($payment, $bill) {
            $payment->delete();
            if ($bill) {
                $bill->recalculate(true);
            }
        });

        return redirect()->route('electricity.index')->with('success', sprintf('Payment of NPR %s removed.', number_format($amount, 2)));
    }

    /**
     * Legacy quick update reading endpoint for single monthly records (backward compatibility).
     */
    public function updateReading(Request $request, MonthlyRecord $monthlyRecord)
    {
        if ($monthlyRecord->user_id !== auth()->id()) {
            abort(403, 'You do not own this record.');
        }

        $validated = $request->validate([
            'prev_electricity_reading' => ['required', 'numeric', 'min:0'],
            'curr_electricity_reading' => ['required', 'numeric', 'gte:prev_electricity_reading'],
            'electricity_rate' => ['required', 'numeric', 'min:0'],
        ], [
            'curr_electricity_reading.gte' => 'Current meter reading cannot be lower than the previous meter reading.',
        ]);

        $monthlyRecord->update([
            'prev_electricity_reading' => $validated['prev_electricity_reading'],
            'curr_electricity_reading' => $validated['curr_electricity_reading'],
            'electricity_rate' => $validated['electricity_rate'],
        ]);

        $monthlyRecord->recalculate(true);

        return redirect()->back()->with('success', 'Electricity readings for ' . $monthlyRecord->formatted_month . ' updated successfully.');
    }
}
