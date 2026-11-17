<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyRecord extends Model
{
    use HasFactory;

    protected $table = 'monthly_records';

    protected $fillable = [
        'year',
        'month',
        'record_date',
        'rent_amount',
        'prev_electricity_reading',
        'curr_electricity_reading',
        'electricity_units',
        'electricity_rate',
        'electricity_amount',
        'total_water_bill',
        'tenant_water_percentage',
        'water_amount',
        'other_charges',
        'total_expenses',
        'total_paid',
        'balance',
        'status',
        'is_historical',
        'is_verified',
        'notes',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'record_date' => 'date',
        'rent_amount' => 'decimal:2',
        'prev_electricity_reading' => 'decimal:2',
        'curr_electricity_reading' => 'decimal:2',
        'electricity_units' => 'decimal:2',
        'electricity_rate' => 'decimal:2',
        'electricity_amount' => 'decimal:2',
        'total_water_bill' => 'decimal:2',
        'tenant_water_percentage' => 'decimal:2',
        'water_amount' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'is_historical' => 'boolean',
        'is_verified' => 'boolean',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderBy('payment_date', 'asc');
    }

    public function additionalExpenses(): HasMany
    {
        return $this->hasMany(AdditionalExpense::class);
    }

    /**
     * Recompute all financial totals based on current attributes and related items
     */
    public function recalculate(bool $save = false): self
    {
        // 1. Electricity calculation
        if ($this->curr_electricity_reading >= $this->prev_electricity_reading) {
            $this->electricity_units = (float)$this->curr_electricity_reading - (float)$this->prev_electricity_reading;
        } else {
            $this->electricity_units = 0.00;
        }
        $this->electricity_amount = round((float)$this->electricity_units * (float)$this->electricity_rate, 2);

        // 2. Water calculation
        $tenantPct = (float)$this->tenant_water_percentage;
        $this->water_amount = round((float)$this->total_water_bill * ($tenantPct / 100), 2);

        // 3. Additional expenses calculation
        if ($this->exists) {
            $this->other_charges = (float)$this->additionalExpenses()->sum('amount');
        }

        // 4. Total monthly expenses
        $this->total_expenses = round(
            (float)$this->rent_amount +
            (float)$this->electricity_amount +
            (float)$this->water_amount +
            (float)$this->other_charges,
            2
        );

        // 5. Total paid from payments
        if ($this->exists) {
            $this->total_paid = (float)$this->payments()->sum('amount');
        }

        // 6. Remaining balance
        $this->balance = round((float)$this->total_expenses - (float)$this->total_paid, 2);

        // 7. Status determination
        if ($this->total_paid <= 0) {
            $this->status = 'unpaid';
        } elseif ($this->total_paid < $this->total_expenses) {
            $this->status = 'partially_paid';
        } elseif ($this->total_paid == $this->total_expenses) {
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
     * Formatted string e.g. "November 2026"
     */
    public function getFormattedMonthAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Short formatted string e.g. "Nov 2026"
     */
    public function getShortMonthAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('M Y');
    }

    /**
     * Owner's share of water bill
     */
    public function getOwnerWaterShareAttribute(): float
    {
        return round((float)$this->total_water_bill - (float)$this->water_amount, 2);
    }

    /**
     * Readable status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'Fully Paid',
            'partially_paid' => 'Partially Paid',
            'overpaid' => 'Overpaid',
            default => 'Unpaid',
        };
    }

    /**
     * Tailwind classes for status badge
     */
    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'partially_paid' => 'bg-amber-100 text-amber-800 border-amber-200',
            'overpaid' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            default => 'bg-rose-100 text-rose-800 border-rose-200',
        };
    }
}
