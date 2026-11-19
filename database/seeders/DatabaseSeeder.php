<?php

namespace Database\Seeders;

use App\Models\AdditionalExpense;
use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use App\Models\User;
use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create default user
        $user = User::updateOrCreate(
            ['email' => 'tenant@rentmate.com'],
            [
                'name' => 'Sajan Poudel',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Seed Rental Settings for the user
        RentalSetting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'rent_amount' => 16000.00,
                'electricity_rate' => 10.00,
                'water_share_percentage' => 50.00,
                'currency' => 'NPR',
                'calendar_preference' => 'both',
                'rental_start_date' => '2024-11-01',
                'owner_name' => 'Ram Bahadur Shrestha',
                'owner_phone' => '+977 9841000000',
                'owner_email' => 'landlord@example.com',
                'property_address' => 'Baneshwor, Kathmandu, Nepal',
                'payment_reminder_days' => 5,
                'notes' => 'Rent due by 5th of every Nepali month.',
            ]
        );

        // 3. Clear existing records if re-seeding (Cross-database compatible: MySQL, PostgreSQL, SQLite)
        $driver = DB::getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = \'replica\';');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        MonthlyRecord::where('user_id', $user->id)->delete();
        Payment::where('user_id', $user->id)->delete();
        AdditionalExpense::where('user_id', $user->id)->delete();

        if ($driver === 'pgsql') {
            DB::statement('SET session_replication_role = \'origin\';');
        } elseif ($driver === 'mysql') {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        // Sample data for past months (Jestha 2083 / May 2026 to Mangsir 2083 / November 2026)
        $records = [
            [
                'year' => 2026,
                'month' => 5,
                'bs_year' => 2083,
                'bs_month' => 2, // Jestha
                'rent_amount' => 16000.00,
                'prev_reading' => 2450.00,
                'curr_reading' => 2520.00,
                'water_bill' => 800.00,
                'is_historical' => true,
                'is_verified' => true,
                'notes' => 'Jestha 2083 - settled on-time',
                'payments' => [
                    ['date' => '2026-05-04', 'amount' => 17100.00, 'method' => 'eSewa', 'ref' => 'ESEWA-MAY-9921', 'notes' => 'Full rent + utilities'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 6,
                'bs_year' => 2083,
                'bs_month' => 3, // Ashadh
                'rent_amount' => 16000.00,
                'prev_reading' => 2520.00,
                'curr_reading' => 2605.00,
                'water_bill' => 900.00,
                'is_historical' => true,
                'is_verified' => true,
                'notes' => 'Ashadh 2083 - cleared in two installments',
                'payments' => [
                    ['date' => '2026-06-03', 'amount' => 10000.00, 'method' => 'Bank Transfer', 'ref' => 'NABIL-TXN-6612', 'notes' => '1st installment'],
                    ['date' => '2026-06-18', 'amount' => 7300.00, 'method' => 'Khalti', 'ref' => 'KHLT-TXN-8812', 'notes' => '2nd installment (cleared)'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 7,
                'bs_year' => 2083,
                'bs_month' => 4, // Shrawan
                'rent_amount' => 16000.00,
                'prev_reading' => 2605.00,
                'curr_reading' => 2700.00,
                'water_bill' => 1000.00,
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'Shrawan 2083 summer utility bills',
                'expenses' => [
                    ['title' => 'Waste Management', 'amount' => 350.00, 'category' => 'Waste'],
                ],
                'payments' => [
                    ['date' => '2026-07-05', 'amount' => 17800.00, 'method' => 'Bank Transfer', 'ref' => 'NABIL-TXN-7102', 'notes' => 'Rent and utilities'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 8,
                'bs_year' => 2083,
                'bs_month' => 5, // Bhadra
                'rent_amount' => 16000.00,
                'prev_reading' => 2700.00,
                'curr_reading' => 2790.00,
                'water_bill' => 850.00,
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'Bhadra 2083',
                'payments' => [
                    ['date' => '2026-08-04', 'amount' => 17325.00, 'method' => 'Cash', 'ref' => null, 'notes' => 'Handed over in cash to owner'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 9,
                'bs_year' => 2083,
                'bs_month' => 6, // Ashwin
                'rent_amount' => 16000.00,
                'prev_reading' => 2790.00,
                'curr_reading' => 2885.00,
                'water_bill' => 920.00,
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'Ashwin 2083',
                'payments' => [
                    ['date' => '2026-09-05', 'amount' => 17410.00, 'method' => 'eSewa', 'ref' => 'ESEWA-SEP-4519', 'notes' => 'Paid on time'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 10,
                'bs_year' => 2083,
                'bs_month' => 7, // Kartik
                'rent_amount' => 16000.00,
                'prev_reading' => 2885.00,
                'curr_reading' => 2980.00,
                'water_bill' => 900.00,
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'Kartik 2083 (Dashain & Tihar) - partial payment',
                'payments' => [
                    ['date' => '2026-10-06', 'amount' => 10000.00, 'method' => 'eSewa', 'ref' => 'ESEWA-OCT-1120', 'notes' => 'Partial installment before holidays'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 11,
                'bs_year' => 2083,
                'bs_month' => 8, // Mangsir (CURRENT MONTH!)
                'rent_amount' => 16000.00,
                'prev_reading' => 2980.00,
                'curr_reading' => 3065.00,
                'water_bill' => 860.00,
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'Mangsir 2083 (Current month running)',
                'expenses' => [
                    ['title' => 'Waste Management', 'amount' => 350.00, 'category' => 'Waste'],
                ],
                'payments' => [], // Unpaid yet!
            ],
        ];

        foreach ($records as $item) {
            $recordDate = Carbon::createFromDate($item['year'], $item['month'], 1)->toDateString();
            $dueDate = Carbon::createFromDate($item['year'], $item['month'], 1)->endOfMonth()->toDateString();
            $monthInfo = NepaliCalendar::MONTHS[$item['bs_month']];
            $nepaliMonthName = $monthInfo['en'] . ' (' . $monthInfo['np'] . ')';

            $record = MonthlyRecord::create([
                'user_id' => $user->id,
                'year' => $item['year'],
                'month' => $item['month'],
                'bs_year' => $item['bs_year'],
                'bs_month' => $item['bs_month'],
                'nepali_month_name' => $nepaliMonthName,
                'record_date' => $recordDate,
                'due_date' => $dueDate,
                'rent_amount' => $item['rent_amount'],
                'prev_electricity_reading' => $item['prev_reading'],
                'curr_electricity_reading' => $item['curr_reading'],
                'electricity_rate' => 10.00,
                'total_water_bill' => $item['water_bill'],
                'tenant_water_percentage' => 50.00,
                'is_historical' => $item['is_historical'],
                'is_verified' => $item['is_verified'],
                'notes' => $item['notes'],
            ]);

            if (!empty($item['expenses'])) {
                foreach ($item['expenses'] as $exp) {
                    $record->additionalExpenses()->create([
                        'user_id' => $user->id,
                        'title' => $exp['title'],
                        'amount' => $exp['amount'],
                        'category' => $exp['category'],
                    ]);
                }
            }

            if (!empty($item['payments'])) {
                foreach ($item['payments'] as $pay) {
                    $bsPay = NepaliCalendar::adToBs($pay['date']);
                    $record->payments()->create([
                        'user_id' => $user->id,
                        'payment_date' => $pay['date'],
                        'bs_payment_date' => $bsPay['formatted'] . ' (' . $bsPay['month_np'] . ')',
                        'amount' => $pay['amount'],
                        'payment_method' => $pay['method'],
                        'transaction_reference' => $pay['ref'],
                        'notes' => $pay['notes'],
                    ]);
                }
            }

            $record->recalculate(true);
        }
    }
}
