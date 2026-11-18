<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdditionalExpense extends Model
{
    use HasFactory;

    protected $table = 'additional_expenses';

    protected $fillable = [
        'user_id',
        'monthly_record_id',
        'title',
        'amount',
        'category',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
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
        static::saved(function ($expense) {
            $expense->monthlyRecord?->recalculate(true);
        });

        static::deleted(function ($expense) {
            $expense->monthlyRecord?->recalculate(true);
        });
    }
}
