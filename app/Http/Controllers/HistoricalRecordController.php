<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\RentalSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class HistoricalRecordController extends Controller
{
    public function index(Request $request)
    {
        $settings = RentalSetting::getActive();

        $query = MonthlyRecord::with('payments')
            ->where('is_historical', true)
            ->orderBy('record_date', 'desc');

        if ($request->filled('verification')) {
            if ($request->verification === 'verified') {
                $query->where('is_verified', true);
            } elseif ($request->verification === 'estimated') {
                $query->where('is_verified', false);
            }
        }

        $records = $query->paginate(12)->withQueryString();

        // Historical statistics
        $totalHistoricalExpenses = (float) MonthlyRecord::where('is_historical', true)->sum('total_expenses');
        $totalHistoricalPaid = (float) MonthlyRecord::where('is_historical', true)->sum('total_paid');
        $totalHistoricalBalance = (float) MonthlyRecord::where('is_historical', true)->sum('balance');
        $verifiedCount = MonthlyRecord::where('is_historical', true)->where('is_verified', true)->count();
        $estimatedCount = MonthlyRecord::where('is_historical', true)->where('is_verified', false)->count();

        return view('historical.index', compact(
            'records',
            'settings',
            'totalHistoricalExpenses',
            'totalHistoricalPaid',
            'totalHistoricalBalance',
            'verifiedCount',
            'estimatedCount'
        ));
    }

    public function create()
    {
        $settings = RentalSetting::getActive();

        // Default to 12 months ago or previous month
        $defaultYear = Carbon::now()->subYear()->year;
        $defaultMonth = 1;

        return view('historical.create', compact('settings', 'defaultYear', 'defaultMonth'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('monthly_records')->where(fn ($query) => $query->where('month', $request->month)),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'prev_electricity_reading' => ['nullable', 'numeric', 'min:0'],
            'curr_electricity_reading' => ['nullable', 'numeric'],
            'electricity_rate' => ['required', 'numeric', 'min:0'],
            'total_water_bill' => ['nullable', 'numeric', 'min:0'],
            'tenant_water_percentage' => ['required', 'numeric', 'between:0,100'],
            'is_verified' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],
            // Optional historical payment
            'payment_amount' => ['nullable', 'numeric', 'min:0.01'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', 'in:Cash,eSewa,Khalti,Bank Transfer,Other'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
        ], [
            'year.unique' => 'A monthly record for this month and year already exists.',
        ]);

        $prevReading = (float) ($validated['prev_electricity_reading'] ?? 0);
        $currReading = (float) ($validated['curr_electricity_reading'] ?? 0);

        if ($currReading > 0 && $currReading < $prevReading) {
            return redirect()->back()->withInput()->withErrors([
                'curr_electricity_reading' => 'Current meter reading cannot be lower than the previous reading.',
            ]);
        }

        DB::transaction(function () use ($validated, $prevReading, $currReading) {
            $recordDate = Carbon::createFromDate($validated['year'], $validated['month'], 1)->toDateString();

            $record = MonthlyRecord::create([
                'year' => $validated['year'],
                'month' => $validated['month'],
                'record_date' => $recordDate,
                'rent_amount' => $validated['rent_amount'],
                'prev_electricity_reading' => $prevReading,
                'curr_electricity_reading' => $currReading,
                'electricity_rate' => $validated['electricity_rate'],
                'total_water_bill' => $validated['total_water_bill'] ?? 0,
                'tenant_water_percentage' => $validated['tenant_water_percentage'],
                'notes' => $validated['notes'] ?? null,
                'is_historical' => true,
                'is_verified' => (bool) $validated['is_verified'],
            ]);

            // If user logged an actual payment they remember
            if (!empty($validated['payment_amount'])) {
                $record->payments()->create([
                    'payment_date' => $validated['payment_date'] ?? $recordDate,
                    'amount' => $validated['payment_amount'],
                    'payment_method' => $validated['payment_method'] ?? 'Cash',
                    'transaction_reference' => $validated['transaction_reference'] ?? null,
                    'notes' => 'Historical payment recorded',
                ]);
            }

            $record->recalculate(true);
        });

        return redirect()->route('historical.index')
            ->with('success', 'Historical record for ' . Carbon::createFromDate($validated['year'], $validated['month'], 1)->format('F Y') . ' saved successfully.');
    }
}
