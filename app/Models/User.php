<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the monthly records for the user.
     */
    public function monthlyRecords(): HasMany
    {
        return $this->hasMany(MonthlyRecord::class);
    }

    /**
     * Get the rental settings for the user.
     */
    public function rentalSetting(): HasOne
    {
        return $this->hasOne(RentalSetting::class);
    }

    /**
     * Get the payments for the user.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the additional expenses for the user.
     */
    public function additionalExpenses(): HasMany
    {
        return $this->hasMany(AdditionalExpense::class);
    }

    /**
     * Get the electricity bills for the user.
     */
    public function electricityBills(): HasMany
    {
        return $this->hasMany(ElectricityBill::class);
    }

    /**
     * Get the electricity payments for the user.
     */
    public function electricityPayments(): HasMany
    {
        return $this->hasMany(ElectricityPayment::class);
    }
}
