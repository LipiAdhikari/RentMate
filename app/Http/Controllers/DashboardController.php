<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $settings = RentalSetting::where('user_id', $userId)->first();
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $todayBs = NepaliCalendar::today();

        // Current month record for this user
        $currentMonthRecord = MonthlyRecord::with(['payments', 'additionalExpenses'])
            ->where('user_id', $userId)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();

        // If current month record doesn't exist, get the most recent recorded month for this user
        $activeDisplayRecord = $currentMonthRecord ?? MonthlyRecord::with(['payments', 'additionalExpenses'])
            ->where('user_id', $userId)
            ->orderBy('record_date', 'desc')
            ->first();

        // All-time aggregate totals dynamically calculated from DB for this user
        $allTimeExpenses = (float) MonthlyRecord::where('user_id', $userId)->sum('total_expenses');
        $allTimePaid = (float) Payment::where('user_id', $userId)->sum('amount');
        $allTimeBalance = (float) MonthlyRecord::where('user_id', $userId)->sum('balance');
        $allTimeRent = (float) MonthlyRecord::where('user_id', $userId)->sum('rent_amount');
        $allTimeElectricity = (float) MonthlyRecord::where('user_id', $userId)->sum('electricity_amount');
        $allTimeWater = (float) MonthlyRecord::where('user_id', $userId)->sum('water_amount');

        // Unpaid or partially paid months for this user
        $unpaidRecords = MonthlyRecord::with('payments')
            ->where('user_id', $userId)
            ->whereIn('status', ['unpaid', 'partially_paid'])
            ->orderBy('record_date', 'asc')
            ->get();

        // Recent payment transactions for this user
        $recentPayments = Payment::with('monthlyRecord')
            ->where('user_id', $userId)
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Monthly trends for Chart.js for this user
        $historyRecords = MonthlyRecord::where('user_id', $userId)
            ->orderBy('record_date', 'asc')
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
            $monthInfo = NepaliCalendar::MONTHS[$rec->bs_month ?? 8] ?? ['en' => 'Mangsir', 'np' => ''];
            $chartLabels[] = $monthInfo['en'] . ' (' . $rec->short_month . ')';
            $chartRent[] = (float) $rec->rent_amount;
            $chartElectricity[] = (float) $rec->electricity_amount;
            $chartWater[] = (float) $rec->water_amount;
            $chartOther[] = (float) $rec->other_charges;
            $chartTotal[] = (float) $rec->total_expenses;
            $chartUnits[] = (float) $rec->electricity_units;
        }

        return view('dashboard.index', compact(
            'settings',
            'todayBs',
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
