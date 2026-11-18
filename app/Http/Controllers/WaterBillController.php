<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\RentalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WaterBillController extends Controller
{
    public function index()
    {
        $userId = auth()->id();
        $settings = RentalSetting::forUser($userId)->first();

        $records = MonthlyRecord::forUser($userId)->orderBy('record_date', 'desc')->get();

        $totalWaterBills = (float) $records->sum('total_water_bill');
        $tenantTotalShare = (float) $records->sum('water_amount');
        $ownerTotalShare = round($totalWaterBills - $tenantTotalShare, 2);
        $avgTenantShare = $records->count() > 0 ? round($tenantTotalShare / $records->count(), 2) : 0;

        // Chart data
        $chartRecords = MonthlyRecord::forUser($userId)->orderBy('record_date', 'asc')->get();
        $chartLabels = [];
        $chartTenantShares = [];
        $chartOwnerShares = [];

        foreach ($chartRecords as $rec) {
            $chartLabels[] = $rec->short_month;
            $chartTenantShares[] = (float) $rec->water_amount;
            $chartOwnerShares[] = (float) $rec->owner_water_share;
        }

        return view('water.index', compact(
            'records',
            'settings',
            'totalWaterBills',
            'tenantTotalShare',
            'ownerTotalShare',
            'avgTenantShare',
            'chartLabels',
            'chartTenantShares',
            'chartOwnerShares'
        ));
    }

    public function updateBill(Request $request, MonthlyRecord $monthlyRecord)
    {
        // Ensure user owns this record
        if ($monthlyRecord->user_id !== auth()->id()) {
            abort(403, 'You do not own this record.');
        }

        $validated = $request->validate([
            'total_water_bill' => ['required', 'numeric', 'min:0'],
            'tenant_water_percentage' => ['required', 'numeric', 'between:0,100'],
        ]);

        $monthlyRecord->update([
            'total_water_bill' => $validated['total_water_bill'],
            'tenant_water_percentage' => $validated['tenant_water_percentage'],
        ]);

        $monthlyRecord->recalculate(true);

        return redirect()->back()->with('success', 'Water bill for ' . $monthlyRecord->formatted_month . ' updated successfully.');
    }
}
