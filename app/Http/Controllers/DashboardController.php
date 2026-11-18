<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $settings = RentalSetting::forUser($userId)->first();
        $now = Carbon::now();
        $currentYear = $now->year;
        $currentMonth = $now->month;
        $todayBs = NepaliCalendar::today();

        // Current month record for this user
        $currentMonthRecord = MonthlyRecord::forUser($userId)
            ->with(['payments', 'additionalExpenses'])
            ->forYearMonth($currentYear, $currentMonth)
            ->first();

        // If current month record doesn't exist, get the most recent recorded month for this user
        $activeDisplayRecord = $currentMonthRecord ?? MonthlyRecord::forUser($userId)
            ->with(['payments', 'additionalExpenses'])
            ->orderBy('record_date', 'desc')
            ->first();

        // All-time aggregate totals - cached for 5 minutes
        $cacheKey = 'dashboard_aggregates_' . $userId;
        $monthlyAggregates = Cache::remember($cacheKey, 300, function () use ($userId) {
            return MonthlyRecord::forUser($userId)
                ->selectRaw('
                    SUM(total_expenses) as total_expenses,
                    SUM(balance) as total_balance,
                    SUM(rent_amount) as total_rent,
                    SUM(electricity_amount) as total_electricity,
                    SUM(water_amount) as total_water
                ')
                ->first();
        });

        $allTimeExpenses = (float) ($monthlyAggregates->total_expenses ?? 0);
        $allTimeBalance = (float) ($monthlyAggregates->total_balance ?? 0);
        $allTimeRent = (float) ($monthlyAggregates->total_rent ?? 0);
        $allTimeElectricity = (float) ($monthlyAggregates->total_electricity ?? 0);
        $allTimeWater = (float) ($monthlyAggregates->total_water ?? 0);
        $allTimePaid = (float) Cache::remember('dashboard_paid_' . $userId, 300, function () use ($userId) {
            return Payment::forUser($userId)->sum('amount');
        });

        // Unpaid or partially paid months for this user
        $unpaidRecords = MonthlyRecord::forUser($userId)
            ->with('payments')
            ->byStatus('unpaid')
            ->orWhere(function ($query) {
                $query->byStatus('partially_paid');
            })
            ->orderBy('record_date', 'asc')
            ->get();

        // Recent payment transactions for this user
        $recentPayments = Payment::forUser($userId)
            ->with('monthlyRecord')
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        // Monthly trends for Chart.js for this user
        $historyRecords = MonthlyRecord::forUser($userId)
            ->chronological()
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
