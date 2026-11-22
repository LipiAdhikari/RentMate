<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ElectricityBill extends Model
{
    use HasFactory;

    protected $table = 'electricity_bills';

    protected $fillable = [
        'user_id',
        'billing_period_start',
        'billing_period_end',
        'previous_meter_reading',
        'current_meter_reading',
        'units_consumed',
        'rate_per_unit',
        'total_amount',
        'paid_amount',
        'balance',
        'status',
        'is_historical',
        'notes',
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'previous_meter_reading' => 'decimal:2',
        'current_meter_reading' => 'decimal:2',
        'units_consumed' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_historical' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(ElectricityBillAllocation::class, 'electricity_bill_id');
    }

    public function monthlyRecords(): BelongsToMany
    {
        return $this->belongsToMany(MonthlyRecord::class, 'electricity_bill_allocations', 'electricity_bill_id', 'monthly_record_id')
            ->withPivot('allocated_amount', 'notes')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(ElectricityPayment::class, 'electricity_bill_id')->orderBy('payment_date', 'asc');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeChronological($query)
    {
        return $query->orderBy('billing_period_start', 'asc');
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('billing_period_start', 'desc');
    }

    /**
     * Recalculate bill units, totals, paid amounts, balance and status.
     */
    public function recalculate(bool $save = false): self
    {
        if ($this->current_meter_reading >= $this->previous_meter_reading) {
            $this->units_consumed = round((float)$this->current_meter_reading - (float)$this->previous_meter_reading, 2);
        } else {
            $this->units_consumed = 0.00;
        }

        $this->total_amount = round((float)$this->units_consumed * (float)$this->rate_per_unit, 2);

        if ($this->exists) {
            $this->paid_amount = round((float)$this->payments()->sum('amount'), 2);
        } else {
            $this->paid_amount = (float)($this->paid_amount ?? 0);
        }

        $this->balance = round((float)$this->total_amount - (float)$this->paid_amount, 2);

        if ($this->paid_amount <= 0) {
            $this->status = 'unpaid';
        } elseif ($this->paid_amount < $this->total_amount) {
            $this->status = 'partially_paid';
        } elseif ($this->paid_amount == $this->total_amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'overpaid';
        }

        if ($save && $this->exists) {
            $this->save();
        }

        return $this;
    }

    /**
     * Total amount currently allocated across monthly records.
     */
    public function allocatedTotal(): float
    {
        return (float) $this->allocations()->sum('allocated_amount');
    }

    /**
     * Unallocated remaining balance of the bill.
     */
    public function unallocatedAmount(): float
    {
        return max(0.00, round((float)$this->total_amount - $this->allocatedTotal(), 2));
    }

    /**
     * Nicely formatted period string e.g. "Sep 2026 – Dec 2026"
     */
    public function getFormattedPeriodAttribute(): string
    {
        if (!$this->billing_period_start || !$this->billing_period_end) {
            return 'N/A';
        }

        $start = Carbon::parse($this->billing_period_start);
        $end = Carbon::parse($this->billing_period_end);

        if ($start->format('Y-m') === $end->format('Y-m')) {
            return $start->format('F Y');
        }

        return $start->format('M Y') . ' – ' . $end->format('M Y');
    }

    /**
     * Number of calendar months covered (inclusive).
     */
    public function getMonthsCountAttribute(): int
    {
        if (!$this->billing_period_start || !$this->billing_period_end) {
            return 1;
        }

        $start = Carbon::parse($this->billing_period_start);
        $end = Carbon::parse($this->billing_period_end);

        return max(1, ($end->year - $start->year) * 12 + ($end->month - $start->month) + 1);
    }
}
