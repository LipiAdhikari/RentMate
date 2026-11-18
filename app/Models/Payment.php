<?php

namespace App\Models;

use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'user_id',
        'monthly_record_id',
        'payment_date',
        'bs_payment_date',
        'amount',
        'days_late',
        'cleared_full_month',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
        'days_late' => 'integer',
        'cleared_full_month' => 'boolean',
    ];

    public function monthlyRecord(): BelongsTo
    {
        return $this->belongsTo(MonthlyRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::creating(function ($payment) {
            // Auto convert payment date to Bikram Sambat
            if (empty($payment->bs_payment_date) && !empty($payment->payment_date)) {
                $bs = NepaliCalendar::adToBs($payment->payment_date);
                $payment->bs_payment_date = $bs['formatted'] . ' (' . $bs['month_np'] . ')';
            }

            // Detect if payment is late relative to the monthly record due date
            if ($payment->monthlyRecord) {
                $dueDate = $payment->monthlyRecord->due_date ?? Carbon::createFromDate($payment->monthlyRecord->year, $payment->monthlyRecord->month, 1)->endOfMonth();
                $payDate = Carbon::parse($payment->payment_date);

                if ($payDate->gt($dueDate)) {
                    $payment->days_late = abs((int) $dueDate->diffInDays($payDate));
                } else {
                    $payment->days_late = 0;
                }
            }
        });

        static::saved(function ($payment) {
            $payment->monthlyRecord?->recalculate(true);
        });

        static::deleted(function ($payment) {
            $payment->monthlyRecord?->recalculate(true);
        });
    }

    public function getMethodBadgeClassAttribute(): string
    {
        return match ($this->payment_method) {
            'eSewa' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'Khalti' => 'bg-purple-50 text-purple-700 border-purple-200',
            'Bank Transfer' => 'bg-blue-50 text-blue-700 border-blue-200',
            'Cash' => 'bg-amber-50 text-amber-700 border-amber-200',
            default => 'bg-gray-50 text-gray-700 border-gray-200',
        };
    }
}
