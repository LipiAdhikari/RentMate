<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $table = 'payments';

    protected $fillable = [
        'monthly_record_id',
        'payment_date',
        'amount',
        'payment_method',
        'transaction_reference',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function monthlyRecord(): BelongsTo
    {
        return $this->belongsTo(MonthlyRecord::class);
    }

    protected static function booted()
    {
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
