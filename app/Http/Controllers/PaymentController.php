<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $settings = RentalSetting::getActive();

        $query = Payment::with('monthlyRecord')->orderBy('payment_date', 'desc')->orderBy('id', 'desc');

        if ($request->filled('method')) {
            $query->where('payment_method', $request->method);
        }

        if ($request->filled('monthly_record_id')) {
            $query->where('monthly_record_id', $request->monthly_record_id);
        }

        $payments = $query->paginate(15)->withQueryString();

        // Summary metrics
        $totalPaid = (float) Payment::sum('amount');
        $esewaTotal = (float) Payment::where('payment_method', 'eSewa')->sum('amount');
        $khaltiTotal = (float) Payment::where('payment_method', 'Khalti')->sum('amount');
        $bankTotal = (float) Payment::where('payment_method', 'Bank Transfer')->sum('amount');
        $cashTotal = (float) Payment::where('payment_method', 'Cash')->sum('amount');

        // All monthly records for the "Add Payment" dropdown
        $monthlyRecords = MonthlyRecord::orderBy('record_date', 'desc')->get();

        return view('payments.index', compact(
            'payments',
            'settings',
            'totalPaid',
            'esewaTotal',
            'khaltiTotal',
            'bankTotal',
            'cashTotal',
            'monthlyRecords'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'monthly_record_id' => ['required', 'exists:monthly_records,id'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:Cash,eSewa,Khalti,Bank Transfer,Other'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $monthlyRecord = MonthlyRecord::findOrFail($validated['monthly_record_id']);

        DB::transaction(function () use ($monthlyRecord, $validated) {
            $monthlyRecord->payments()->create($validated);
            $monthlyRecord->recalculate(true);
        });

        return redirect()->back()->with('success', 'Payment of ' . $monthlyRecord->currency . ' ' . number_format($validated['amount'], 2) . ' recorded for ' . $monthlyRecord->formatted_month . '.');
    }

    public function destroy(Payment $payment)
    {
        $monthlyRecord = $payment->monthlyRecord;
        $amount = $payment->amount;

        DB::transaction(function () use ($payment, $monthlyRecord) {
            $payment->delete();
            $monthlyRecord?->recalculate(true);
        });

        return redirect()->back()->with('success', 'Payment transaction of ' . number_format($amount, 2) . ' deleted successfully.');
    }
}
