<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\RentalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ElectricityController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $settings = RentalSetting::forUser($userId)->first();

        $records = MonthlyRecord::forUser($userId)->orderBy('record_date', 'desc')->get();

        $totalUnits = (float) $records->sum('electricity_units');
        $totalCost = (float) $records->sum('electricity_amount');
        $avgUnits = $records->count() > 0 ? round($totalUnits / $records->count(), 1) : 0;
        $avgCost = $records->count() > 0 ? round($totalCost / $records->count(), 2) : 0;

        $latestRecord = $records->first();

        // Chart data chronologically
        $chartRecords = MonthlyRecord::forUser($userId)->orderBy('record_date', 'asc')->get();
        $chartLabels = [];
        $chartUnits = [];
        $chartCosts = [];

        foreach ($chartRecords as $rec) {
            $chartLabels[] = $rec->short_month;
            $chartUnits[] = (float) $rec->electricity_units;
            $chartCosts[] = (float) $rec->electricity_amount;
        }

        return view('electricity.index', compact(
            'records',
            'settings',
            'totalUnits',
            'totalCost',
            'avgUnits',
            'avgCost',
            'latestRecord',
            'chartLabels',
            'chartUnits',
            'chartCosts'
        ));
    }

    public function updateReading(Request $request, MonthlyRecord $monthlyRecord)
    {
        // Ensure user owns this record
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
