<?php

namespace App\Http\Controllers;

use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $settings = RentalSetting::forUser($userId)->first();

        $query = Payment::forUser($userId)
            ->with('monthlyRecord')
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc');

        if ($request->filled('method')) {
            $query->byMethod($request->method);
        }

        if ($request->filled('monthly_record_id')) {
            $query->where('monthly_record_id', $request->monthly_record_id);
        }

        $payments = $query->paginate(15)->withQueryString();

        // Summary metrics for this user - cached for 5 minutes
        $cacheKey = 'payment_summary_' . $userId;
        $paymentSummary = Cache::remember($cacheKey, 300, function () use ($userId) {
            return [
                'totalPaid' => (float) Payment::forUser($userId)->sum('amount'),
                'esewaTotal' => (float) Payment::forUser($userId)->byMethod('eSewa')->sum('amount'),
                'khaltiTotal' => (float) Payment::forUser($userId)->byMethod('Khalti')->sum('amount'),
                'bankTotal' => (float) Payment::forUser($userId)->byMethod('Bank Transfer')->sum('amount'),
                'cashTotal' => (float) Payment::forUser($userId)->byMethod('Cash')->sum('amount'),
            ];
        });

        $totalPaid = $paymentSummary['totalPaid'];
        $esewaTotal = $paymentSummary['esewaTotal'];
        $khaltiTotal = $paymentSummary['khaltiTotal'];
        $bankTotal = $paymentSummary['bankTotal'];
        $cashTotal = $paymentSummary['cashTotal'];

        // All monthly records for this user for the "Add Payment" dropdown
        $monthlyRecords = MonthlyRecord::forUser($userId)
            ->latestFirst()
            ->get();

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
        // Support BS calendar inputs (Year, Month, Day)
        if ($request->filled('bs_year') && $request->filled('bs_month') && $request->filled('bs_day')) {
            $adDateStr = NepaliCalendar::bsToAd(
                (int) $request->bs_year,
                (int) $request->bs_month,
                (int) $request->bs_day
            );
            $request->merge(['payment_date' => $adDateStr]);
        }

        $validated = $request->validate([
            'monthly_record_id' => ['nullable'],
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_method' => ['required', 'in:Cash,eSewa,Khalti,Bank Transfer,Other'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Auto-allocate to oldest unpaid month if requested or empty
        if (empty($validated['monthly_record_id']) || $validated['monthly_record_id'] === 'auto') {
            $targetRecord = MonthlyRecord::forUser(auth()->id())
                ->whereIn('status', ['unpaid', 'partially_paid'])
                ->orderBy('record_date', 'asc')
                ->first();

            if (!$targetRecord) {
                $targetRecord = MonthlyRecord::forUser(auth()->id())
                    ->latestFirst()
                    ->firstOrFail();
            }
        } else {
            $targetRecord = MonthlyRecord::forUser(auth()->id())
                ->findOrFail($validated['monthly_record_id']);
        }

        $paymentDate = Carbon::parse($validated['payment_date']);
        $bsDateInfo = NepaliCalendar::adToBs($paymentDate);
        $bsPaymentDate = $bsDateInfo['formatted'] . ' (' . $bsDateInfo['month_np'] . ')';

        // Calculate days late relative to the target month's due date
        $dueDate = $targetRecord->due_date ?? Carbon::createFromDate($targetRecord->year, $targetRecord->month, 1)->endOfMonth();
        $daysLate = 0;
        if ($paymentDate->gt($dueDate)) {
            $daysLate = abs((int) $dueDate->diffInDays($paymentDate));
        }

        DB::transaction(function () use ($targetRecord, $validated, $bsPaymentDate, $daysLate) {
            $targetRecord->payments()->create([
                'user_id' => auth()->id(),
                'payment_date' => $validated['payment_date'],
                'bs_payment_date' => $bsPaymentDate,
                'amount' => $validated['amount'],
                'days_late' => $daysLate,
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $targetRecord->recalculate(true);
        });

        $targetRecord->refresh();

        // Clear payment cache for this user
        Cache::forget('payment_summary_' . auth()->id());
        // Clear dashboard cache too since payment affects dashboard
        Cache::forget('dashboard_aggregates_' . auth()->id());
        Cache::forget('dashboard_paid_' . auth()->id());

        // Build notification message highlighting the smart detection
        $formattedAmount = number_format($validated['amount'], 2);
        $monthLabel = $targetRecord->dual_month_label;

        if ($targetRecord->status === 'paid' || $targetRecord->status === 'overpaid') {
            $lateText = $daysLate > 0 ? " (Paid {$daysLate} days after due date on {$paymentDate->format('M d, Y')})" : ' (Paid on-time)';
            $msg = "Success: Payment of NPR {$formattedAmount} detected and applied to {$monthLabel}. 1 full month rent is now CLEARED and fully settled{$lateText}!";
        } else {
            $rem = number_format($targetRecord->balance, 2);
            $msg = "Payment of NPR {$formattedAmount} applied to {$monthLabel}. Remaining balance: NPR {$rem}.";
        }

        return redirect()->back()->with('success', $msg);
    }

    public function update(Request $request, Payment $payment)
    {
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'You do not own this payment.');
        }

        // Support BS calendar inputs (Year, Month, Day)
        if ($request->filled('bs_year') && $request->filled('bs_month') && $request->filled('bs_day')) {
            $adDateStr = NepaliCalendar::bsToAd(
                (int) $request->bs_year,
                (int) $request->bs_month,
                (int) $request->bs_day
            );
            $request->merge(['payment_date' => $adDateStr]);
        }

        $validated = $request->validate([
            'payment_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:Cash,eSewa,Khalti,Bank Transfer,Other'],
            'transaction_reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $paymentDate = Carbon::parse($validated['payment_date']);
        $bsDateInfo = NepaliCalendar::adToBs($paymentDate);
        $bsPaymentDate = $bsDateInfo['formatted'] . ' (' . $bsDateInfo['month_np'] . ')';

        $targetRecord = $payment->monthlyRecord;
        $daysLate = 0;
        if ($targetRecord) {
            $dueDate = $targetRecord->due_date ?? Carbon::createFromDate($targetRecord->year, $targetRecord->month, 1)->endOfMonth();
            if ($paymentDate->gt($dueDate)) {
                $daysLate = abs((int) $dueDate->diffInDays($paymentDate));
            }
        }

        DB::transaction(function () use ($payment, $validated, $bsPaymentDate, $daysLate, $targetRecord) {
            $payment->update([
                'payment_date' => $validated['payment_date'],
                'bs_payment_date' => $bsPaymentDate,
                'amount' => $validated['amount'],
                'days_late' => $daysLate,
                'payment_method' => $validated['payment_method'],
                'transaction_reference' => $validated['transaction_reference'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $targetRecord?->recalculate(true);
        });

        Cache::forget('payment_summary_' . auth()->id());
        Cache::forget('dashboard_aggregates_' . auth()->id());
        Cache::forget('dashboard_paid_' . auth()->id());

        return redirect()->back()->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        // Ensure user owns this payment
        if ($payment->user_id !== auth()->id()) {
            abort(403, 'You do not own this payment.');
        }

        $monthlyRecord = $payment->monthlyRecord;
        $amount = $payment->amount;

        DB::transaction(function () use ($payment, $monthlyRecord) {
            $payment->delete();
            $monthlyRecord?->recalculate(true);
        });

        // Clear payment cache for this user
        Cache::forget('payment_summary_' . auth()->id());
        Cache::forget('dashboard_aggregates_' . auth()->id());
        Cache::forget('dashboard_paid_' . auth()->id());

        return redirect()->back()->with('success', 'Payment transaction of NPR ' . number_format($amount, 2) . ' deleted successfully.');
    }
}
