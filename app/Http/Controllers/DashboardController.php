<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $settings = RentalSetting::getActive();
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;

        // Current month record
        $currentMonthRecord = MonthlyRecord::with(['payments', 'additionalExpenses'])
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        // If current month record doesn't exist, get the most recent recorded month
        $activeDisplayRecord = $currentMonthRecord ?? MonthlyRecord::with(['payments', 'additionalExpenses'])
            ->orderBy('record_date', 'desc')
            ->first();

        // All-time aggregate totals dynamically calculated from DB
        $allTimeExpenses = (float) MonthlyRecord::sum('total_expenses');
        $allTimePaid = (float) Payment::sum('amount');
        $allTimeBalance = (float) MonthlyRecord::sum('balance');
        $allTimeRent = (float) MonthlyRecord::sum('rent_amount');
        $allTimeElectricity = (float) MonthlyRecord::sum('electricity_amount');
        $allTimeWater = (float) MonthlyRecord::sum('water_amount');

        // Unpaid or partially paid months
        $unpaidRecords = MonthlyRecord::with('payments')
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('record_date', 'asc')
            ->get();

        // Recent payment transactions (last 6)
        $recentPayments = Payment::with('monthlyRecord')
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Monthly trends for Chart.js (last 12 months ordered chronologically)
        $historyRecords = MonthlyRecord::orderBy('record_date', 'asc')
            ->limit(12)
            ->get();

        $chartLabels = [];
        $chartRent = [];
        $chartElectricity = [];
        $chartWater = [];
        $chartOther = [];
        $chartTotal = [];
        $chartUnits = [];

        foreach ($historyRecords as $rec) {
            $chartLabels[] = $rec->short_month;
            $chartRent[] = (float) $rec->rent_amount;
            $chartElectricity[] = (float) $rec->electricity_amount;
            $chartWater[] = (float) $rec->water_amount;
            $chartOther[] = (float) $rec->other_charges;
            $chartTotal[] = (float) $rec->total_expenses;
            $chartUnits[] = (float) $rec->electricity_units;
        }

        return view('dashboard.index', compact(
            'settings',
            'currentMonthRecord',
            'activeDisplayRecord',
            'allTimeExpenses',
            'allTimePaid',
            'allTimeBalance',
            'allTimeRent',
            'allTimeElectricity',
            'allTimeWater',
            'unpaidRecords',
            'recentPayments',
            'chartLabels',
            'chartRent',
            'chartElectricity',
            'chartWater',
            'chartOther',
            'chartTotal',
            'chartUnits'
        ));
    }
}
