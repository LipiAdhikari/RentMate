<?php

namespace App\Models;

use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyRecord extends Model
{
    use HasFactory;

    protected $table = 'monthly_records';

    protected $fillable = [
        'user_id',
        'year',
        'month',
        'bs_year',
        'bs_month',
        'nepali_month_name',
        'record_date',
        'due_date',
        'cleared_at',
        'settlement_notes',
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
        'bs_year' => 'integer',
        'bs_month' => 'integer',
        'record_date' => 'date',
        'due_date' => 'date',
        'cleared_at' => 'date',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::saving(function ($record) {
            // Auto sync Nepali calendar if not provided
            if (empty($record->bs_year) || empty($record->bs_month)) {
                $bs = NepaliCalendar::guessBsMonthFromAd($record->year, $record->month);
                $record->bs_year = $bs['bs_year'];
                $record->bs_month = $bs['bs_month'];
                $record->nepali_month_name = $bs['month_name'];
            }

            // Auto calculate due date if not set (due by end of rental month, e.g. Jan 31)
            if (empty($record->due_date)) {
                $record->due_date = Carbon::createFromDate($record->year, $record->month, 1)->endOfMonth()->toDateString();
            }
        });
    }

    /**
     * Recompute all financial totals and smart late settlement detection
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

        // 7. Status determination and Smart 1-Month-Paid Detection
        $prevStatus = $this->status;

        if ($this->total_paid <= 0) {
            $this->status = 'unpaid';
            $this->cleared_at = null;
            $this->settlement_notes = null;
        } elseif ($this->total_paid < $this->total_expenses) {
            $this->status = 'partially_paid';
            $this->cleared_at = null;
            $this->settlement_notes = sprintf('Partially paid: NPR %s / %s', number_format($this->total_paid, 2), number_format($this->total_expenses, 2));
        } elseif ($this->total_paid >= $this->total_expenses) {
            $this->status = $this->total_paid == $this->total_expenses ? 'paid' : 'overpaid';

            // Automatic settlement timing detection
            $latestPayment = $this->payments()->orderBy('payment_date', 'desc')->first();
            $settleDate = $latestPayment ? $latestPayment->payment_date : Carbon::now();
            $this->cleared_at = $settleDate;

            $dueDate = $this->due_date ?? Carbon::createFromDate($this->year, $this->month, 1)->endOfMonth();

            if ($settleDate->gt($dueDate)) {
                $daysLate = abs((int) $dueDate->diffInDays($settleDate));
                $this->settlement_notes = sprintf(
                    '1 Month Rent fully paid on %s (%d days after due date)',
                    $settleDate->format('M d, Y'),
                    $daysLate
                );
            } else {
                $this->settlement_notes = sprintf(
                    '1 Month Rent fully paid on-time on %s',
                    $settleDate->format('M d, Y')
                );
            }
        }

        if ($save && $this->exists) {
            $this->save();
        }

        return $this;
    }

    /**
     * Formatted English string e.g. "November 2026"
     */
    public function getFormattedMonthAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Short English formatted string e.g. "Nov 2026"
     */
    public function getShortMonthAttribute(): string
    {
        return Carbon::createFromDate($this->year, $this->month, 1)->format('M Y');
    }

    /**
     * Dual Nepali & English Month Label
     * e.g. "Mangsir 2083 (मंसिर २०८३) • Nov 2026"
     */
    public function getDualMonthLabelAttribute(): string
    {
        return NepaliCalendar::getDualMonthLabel(
            $this->year,
            $this->month,
            $this->bs_year,
            $this->bs_month
        );
    }

    /**
     * Nepali Month String e.g. "Mangsir 2083 (मंसिर २०८३)"
     */
    public function getNepaliMonthFormattedAttribute(): string
    {
        $bsYear = $this->bs_year ?? ($this->year + 57);
        $monthInfo = NepaliCalendar::MONTHS[$this->bs_month ?? 8] ?? ['en' => 'Mangsir', 'np' => 'मंसिर'];

        return sprintf('%s %d (%s %s)', $monthInfo['en'], $bsYear, $monthInfo['np'], NepaliCalendar::toNepaliNumerals($bsYear));
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
            'paid' => 'Fully Paid (1 Mo)',
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
