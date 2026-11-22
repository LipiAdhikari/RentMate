<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ElectricityBillAllocation extends Model
{
    use HasFactory;

    protected $table = 'electricity_bill_allocations';

    protected $fillable = [
        'electricity_bill_id',
        'monthly_record_id',
        'allocated_amount',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(ElectricityBill::class, 'electricity_bill_id');
    }

    public function monthlyRecord(): BelongsTo
    {
        return $this->belongsTo(MonthlyRecord::class, 'monthly_record_id');
    }

    protected static function booted()
    {
        static::saved(function ($allocation) {
            if ($allocation->monthlyRecord) {
                $allocation->monthlyRecord->recalculate(true);
            }
        });

        static::deleted(function ($allocation) {
            if ($allocation->monthlyRecord) {
                $allocation->monthlyRecord->recalculate(true);
            }
        });
    }
}
