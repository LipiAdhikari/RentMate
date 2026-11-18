<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\RentalSetting;
use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MonthlyRecordController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();

        $query = MonthlyRecord::forUser($userId)
            ->with(['payments', 'additionalExpenses'])
            ->orderBy('record_date', 'desc');

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('bs_year')) {
            $query->where('bs_year', $request->bs_year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $records = $query->paginate(12)->withQueryString();
        $settings = RentalSetting::forUser($userId)->first();

        $availableYears = MonthlyRecord::forUser($userId)
            ->select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        $availableBsYears = MonthlyRecord::forUser($userId)
            ->whereNotNull('bs_year')
            ->select('bs_year')
            ->distinct()
            ->orderBy('bs_year', 'desc')
            ->pluck('bs_year');

        return view('monthly-records.index', compact('records', 'settings', 'availableYears', 'availableBsYears'));
    }

    public function create()
    {
        $settings = RentalSetting::forUser(auth()->id())->first();
        $latestRecord = MonthlyRecord::forUser(auth()->id())->orderBy('record_date', 'desc')->first();

        if ($latestRecord) {
            $nextDate = Carbon::createFromDate($latestRecord->year, $latestRecord->month, 1)->addMonth();
            $defaultYear = $nextDate->year;
            $defaultMonth = $nextDate->month;
            $defaultPrevReading = $latestRecord->curr_electricity_reading;
        } else {
            $now = Carbon::now();
            $defaultYear = $now->year;
            $defaultMonth = $now->month;
            $defaultPrevReading = 0.00;
        }

        $bsInfo = NepaliCalendar::adToBs(sprintf('%04d-%02d-01', $defaultYear, $defaultMonth));
        $defaultBsYear = $bsInfo['year'];
        $defaultBsMonth = $bsInfo['month'];
        $nepaliMonths = NepaliCalendar::MONTHS;

        return view('monthly-records.create', compact(
            'settings',
            'defaultYear',
            'defaultMonth',
            'defaultBsYear',
            'defaultBsMonth',
            'defaultPrevReading',
            'nepaliMonths'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('monthly_records')->where(fn ($query) => $query
                    ->where('user_id', auth()->id())
                    ->where('month', $request->month)),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'bs_year' => ['nullable', 'integer', 'between:2070,2100'],
            'bs_month' => ['nullable', 'integer', 'between:1,12'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'prev_electricity_reading' => ['required', 'numeric', 'min:0'],
            'curr_electricity_reading' => ['required', 'numeric', 'gte:prev_electricity_reading'],
            'electricity_rate' => ['required', 'numeric', 'min:0'],
            'total_water_bill' => ['required', 'numeric', 'min:0'],
            'tenant_water_percentage' => ['required', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
            'expenses' => ['nullable', 'array'],
            'expenses.*.title' => ['required_with:expenses.*.amount', 'string', 'max:255'],
            'expenses.*.amount' => ['required_with:expenses.*.title', 'numeric', 'min:0.01'],
            'expenses.*.category' => ['nullable', 'string', 'max:100'],
        ], [
            'year.unique' => 'A monthly record for this month and year already exists.',
            'curr_electricity_reading.gte' => 'Current meter reading cannot be lower than the previous meter reading.',
        ]);

        $record = DB::transaction(function () use ($validated, $request) {
            $recordDate = Carbon::createFromDate($validated['year'], $validated['month'], 1)->toDateString();
            $dueDate = Carbon::createFromDate($validated['year'], $validated['month'], 1)->endOfMonth()->toDateString();

            $bsYear = $validated['bs_year'] ?? null;
            $bsMonth = $validated['bs_month'] ?? null;
            if (!$bsYear || !$bsMonth) {
                $bs = NepaliCalendar::guessBsMonthFromAd($validated['year'], $validated['month']);
                $bsYear = $bs['bs_year'];
                $bsMonth = $bs['bs_month'];
            }
            $monthInfo = NepaliCalendar::MONTHS[$bsMonth] ?? ['en' => 'Month ' . $bsMonth, 'np' => ''];
            $nepaliMonthName = $monthInfo['en'] . ' (' . $monthInfo['np'] . ')';

            $record = MonthlyRecord::create([
                'user_id' => auth()->id(),
                'year' => $validated['year'],
                'month' => $validated['month'],
                'bs_year' => $bsYear,
                'bs_month' => $bsMonth,
                'nepali_month_name' => $nepaliMonthName,
                'record_date' => $recordDate,
                'due_date' => $dueDate,
                'rent_amount' => $validated['rent_amount'],
                'prev_electricity_reading' => $validated['prev_electricity_reading'],
                'curr_electricity_reading' => $validated['curr_electricity_reading'],
                'electricity_rate' => $validated['electricity_rate'],
                'total_water_bill' => $validated['total_water_bill'],
                'tenant_water_percentage' => $validated['tenant_water_percentage'],
                'notes' => $validated['notes'] ?? null,
                'is_historical' => false,
                'is_verified' => true,
            ]);

            if ($request->has('expenses') && is_array($request->expenses)) {
                foreach ($request->expenses as $expense) {
                    if (!empty($expense['title']) && !empty($expense['amount'])) {
                        $record->additionalExpenses()->create([
                            'user_id' => auth()->id(),
                            'title' => $expense['title'],
                            'amount' => $expense['amount'],
                            'category' => $expense['category'] ?? 'maintenance',
                        ]);
                    }
                }
            }

            $record->recalculate(true);

            return $record;
        });

        return redirect()->route('monthly-records.show', $record)
            ->with('success', 'Monthly record for ' . $record->dual_month_label . ' created successfully.');
    }

    public function show(MonthlyRecord $monthlyRecord)
    {
        if ($monthlyRecord->user_id !== auth()->id()) {
            abort(403, 'You do not own this record.');
        }

        $monthlyRecord->load(['payments', 'additionalExpenses']);
        $settings = RentalSetting::forUser(auth()->id())->first();

        return view('monthly-records.show', compact('monthlyRecord', 'settings'));
    }

    public function edit(MonthlyRecord $monthlyRecord)
    {
        if ($monthlyRecord->user_id !== auth()->id()) {
            abort(403, 'You do not own this record.');
        }

        $monthlyRecord->load('additionalExpenses');
        $settings = RentalSetting::forUser(auth()->id())->first();
        $nepaliMonths = NepaliCalendar::MONTHS;

        return view('monthly-records.edit', compact('monthlyRecord', 'settings', 'nepaliMonths'));
    }

    public function update(Request $request, MonthlyRecord $monthlyRecord)
    {
        if ($monthlyRecord->user_id !== auth()->id()) {
            abort(403, 'You do not own this record.');
        }

        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('monthly_records')->where(fn ($query) => $query
                    ->where('user_id', auth()->id())
                    ->where('month', $request->month))
                    ->ignore($monthlyRecord->id),
            ],
            'month' => ['required', 'integer', 'between:1,12'],
            'bs_year' => ['nullable', 'integer', 'between:2070,2100'],
            'bs_month' => ['nullable', 'integer', 'between:1,12'],
            'rent_amount' => ['required', 'numeric', 'min:0'],
            'prev_electricity_reading' => ['required', 'numeric', 'min:0'],
            'curr_electricity_reading' => ['required', 'numeric', 'gte:prev_electricity_reading'],
            'electricity_rate' => ['required', 'numeric', 'min:0'],
            'total_water_bill' => ['required', 'numeric', 'min:0'],
            'tenant_water_percentage' => ['required', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string'],
            'expenses' => ['nullable', 'array'],
            'expenses.*.title' => ['required_with:expenses.*.amount', 'string', 'max:255'],
            'expenses.*.amount' => ['required_with:expenses.*.title', 'numeric', 'min:0.01'],
            'expenses.*.category' => ['nullable', 'string', 'max:100'],
        ], [
            'year.unique' => 'Another monthly record for this month and year already exists.',
            'curr_electricity_reading.gte' => 'Current meter reading cannot be lower than the previous meter reading.',
        ]);

        DB::transaction(function () use ($validated, $request, $monthlyRecord) {
            $recordDate = Carbon::createFromDate($validated['year'], $validated['month'], 1)->toDateString();

            $bsYear = $validated['bs_year'] ?? null;
            $bsMonth = $validated['bs_month'] ?? null;
            if (!$bsYear || !$bsMonth) {
                $bs = NepaliCalendar::guessBsMonthFromAd($validated['year'], $validated['month']);
                $bsYear = $bs['bs_year'];
                $bsMonth = $bs['bs_month'];
            }
            $monthInfo = NepaliCalendar::MONTHS[$bsMonth] ?? ['en' => 'Month ' . $bsMonth, 'np' => ''];
            $nepaliMonthName = $monthInfo['en'] . ' (' . $monthInfo['np'] . ')';

            $monthlyRecord->update([
                'user_id' => auth()->id(),
                'year' => $validated['year'],
                'month' => $validated['month'],
                'bs_year' => $bsYear,
                'bs_month' => $bsMonth,
                'nepali_month_name' => $nepaliMonthName,
                'record_date' => $recordDate,
                'rent_amount' => $validated['rent_amount'],
                'prev_electricity_reading' => $validated['prev_electricity_reading'],
                'curr_electricity_reading' => $validated['curr_electricity_reading'],
                'electricity_rate' => $validated['electricity_rate'],
                'total_water_bill' => $validated['total_water_bill'],
                'tenant_water_percentage' => $validated['tenant_water_percentage'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $monthlyRecord->additionalExpenses()->delete();
            if ($request->has('expenses') && is_array($request->expenses)) {
                foreach ($request->expenses as $expense) {
                    if (!empty($expense['title']) && !empty($expense['amount'])) {
                        $monthlyRecord->additionalExpenses()->create([
                            'user_id' => auth()->id(),
                            'title' => $expense['title'],
                            'amount' => $expense['amount'],
                            'category' => $expense['category'] ?? 'maintenance',
                        ]);
                    }
                }
            }

            $monthlyRecord->recalculate(true);
        });

        return redirect()->route('monthly-records.show', $monthlyRecord)
            ->with('success', 'Monthly record for ' . $monthlyRecord->dual_month_label . ' updated successfully.');
    }

    public function destroy(MonthlyRecord $monthlyRecord)
    {
        if ($monthlyRecord->user_id !== auth()->id()) {
            abort(403, 'You do not own this record.');
        }

        $monthName = $monthlyRecord->dual_month_label;
        $monthlyRecord->delete();

        return redirect()->route('monthly-records.index')
            ->with('success', 'Record for ' . $monthName . ' and its associated payments were deleted.');
    }
}
