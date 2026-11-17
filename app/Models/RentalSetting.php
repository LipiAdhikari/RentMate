<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentalSetting extends Model
{
    use HasFactory;

    protected $table = 'rental_settings';

    protected $fillable = [
        'rent_amount',
        'electricity_rate',
        'water_share_percentage',
        'currency',
        'rental_start_date',
        'owner_name',
        'owner_phone',
        'owner_email',
        'property_address',
        'payment_reminder_days',
        'notes',
    ];

    protected $casts = [
        'rent_amount' => 'decimal:2',
        'electricity_rate' => 'decimal:2',
        'water_share_percentage' => 'decimal:2',
        'rental_start_date' => 'date',
        'payment_reminder_days' => 'integer',
    ];

    /**
     * Get or create active settings singleton instance
     */
    public static function getActive(): self
    {
        return self::firstOrCreate(
            ['id' => 1],
            [
                'rent_amount' => 16000.00,
                'electricity_rate' => 10.00,
                'water_share_percentage' => 50.00,
                'currency' => 'NPR',
                'rental_start_date' => '2024-11-01',
                'owner_name' => 'House Owner',
                'property_address' => 'Kathmandu, Nepal',
                'payment_reminder_days' => 5,
            ]
        );
    }
}
