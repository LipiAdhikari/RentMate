<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $settings = RentalSetting::getActive();

        $query = MonthlyRecord::with(['payments', 'additionalExpenses'])
            ->orderBy('record_date', 'asc');

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        if ($request->filled('from_date')) {
            $query->where('record_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->where('record_date', '<=', $request->to_date);
        }

        $records = $query->get();

        // Aggregates
        $totalRent = (float) $records->sum('rent_amount');
        $totalElectricity = (float) $records->sum('electricity_amount');
        $totalWater = (float) $records->sum('water_amount');
        $totalOther = (float) $records->sum('other_charges');
        $totalExpenses = (float) $records->sum('total_expenses');
        $totalPaid = (float) $records->sum('total_paid');
        $totalBalance = (float) $records->sum('balance');
        $totalUnits = (float) $records->sum('electricity_units');

        // Available years
        $availableYears = MonthlyRecord::select('year')->distinct()->orderBy('year', 'desc')->pluck('year');

        // Chart Data
        $chartLabels = [];
        $chartRent = [];
        $chartElectricity = [];
        $chartWater = [];
        $chartOther = [];
        $chartTotal = [];
        $chartPaid = [];
        $chartUnits = [];

        foreach ($records as $r) {
            $chartLabels[] = $r->short_month;
            $chartRent[] = (float) $r->rent_amount;
            $chartElectricity[] = (float) $r->electricity_amount;
            $chartWater[] = (float) $r->water_amount;
            $chartOther[] = (float) $r->other_charges;
            $chartTotal[] = (float) $r->total_expenses;
            $chartPaid[] = (float) $r->total_paid;
            $chartUnits[] = (float) $r->electricity_units;
        }

        return view('reports.index', compact(
            'records',
            'settings',
            'totalRent',
            'totalElectricity',
            'totalWater',
            'totalOther',
            'totalExpenses',
            'totalPaid',
            'totalBalance',
            'totalUnits',
            'availableYears',
            'chartLabels',
            'chartRent',
            'chartElectricity',
            'chartWater',
            'chartOther',
            'chartTotal',
            'chartPaid',
            'chartUnits'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $query = MonthlyRecord::with('payments')->orderBy('record_date', 'asc');

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $records = $query->get();
        $filename = 'RentMate_Expense_Report_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($records) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Header row
            fputcsv($handle, [
                'Month & Year',
                'Rent (NPR)',
                'Prev Meter Reading',
                'Curr Meter Reading',
                'Electricity Units',
                'Electricity Rate (NPR)',
                'Electricity Bill (NPR)',
                'Total Water Bill (NPR)',
                'Tenant Water Share %',
                'Tenant Water (NPR)',
                'Other Charges (NPR)',
                'Total Expenses (NPR)',
                'Total Paid (NPR)',
                'Remaining Balance (NPR)',
                'Payment Status',
                'Record Type',
                'Notes',
            ]);

            foreach ($records as $r) {
                fputcsv($handle, [
                    $r->formatted_month,
                    number_format($r->rent_amount, 2, '.', ''),
                    $r->prev_electricity_reading,
                    $r->curr_electricity_reading,
                    $r->electricity_units,
                    $r->electricity_rate,
                    number_format($r->electricity_amount, 2, '.', ''),
                    number_format($r->total_water_bill, 2, '.', ''),
                    $r->tenant_water_percentage . '%',
                    number_format($r->water_amount, 2, '.', ''),
                    number_format($r->other_charges, 2, '.', ''),
                    number_format($r->total_expenses, 2, '.', ''),
                    number_format($r->total_paid, 2, '.', ''),
                    number_format($r->balance, 2, '.', ''),
                    $r->status_label,
                    $r->is_historical ? ($r->is_verified ? 'Historical (Verified)' : 'Historical (Estimated)') : 'Current Record',
                    $r->notes ?? '',
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $settings = RentalSetting::getActive();

        $query = MonthlyRecord::with(['payments', 'additionalExpenses'])->orderBy('record_date', 'asc');

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $records = $query->get();

        $totalRent = (float) $records->sum('rent_amount');
        $totalElectricity = (float) $records->sum('electricity_amount');
        $totalWater = (float) $records->sum('water_amount');
        $totalOther = (float) $records->sum('other_charges');
        $totalExpenses = (float) $records->sum('total_expenses');
        $totalPaid = (float) $records->sum('total_paid');
        $totalBalance = (float) $records->sum('balance');

        $pdf = Pdf::loadView('reports.pdf', compact(
            'records',
            'settings',
            'totalRent',
            'totalElectricity',
            'totalWater',
            'totalOther',
            'totalExpenses',
            'totalPaid',
            'totalBalance'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('RentMate_Statement_' . date('Y_m_d') . '.pdf');
    }
}
