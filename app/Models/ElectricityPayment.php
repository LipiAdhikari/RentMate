<?php

namespace App\Models;

use App\Services\NepaliCalendar;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectricityPayment extends Model
{
    use HasFactory;

    protected $table = 'electricity_payments';

    protected $fillable = [
        'electricity_bill_id',
        'user_id',
        'payment_date',
        'bs_payment_date',
        'amount',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(ElectricityBill::class, 'electricity_bill_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    protected static function booted()
    {
        static::saving(function ($payment) {
            // Auto fill bs_payment_date if empty
            if (empty($payment->bs_payment_date) && !empty($payment->payment_date)) {
                $bs = NepaliCalendar::adToBs($payment->payment_date);
                $payment->bs_payment_date = $bs['formatted'] . ' (' . $bs['month_np'] . ')';
            }
        });

        static::saved(function ($payment) {
            if ($payment->bill) {
                $payment->bill->recalculate(true);
            }
        });

        static::deleted(function ($payment) {
            if ($payment->bill) {
                $payment->bill->recalculate(true);
            }
        });
    }
}
