<?php

namespace Database\Seeders;

use App\Models\AdditionalExpense;
use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Rental Settings
        RentalSetting::updateOrCreate(
            ['id' => 1],
            [
                'rent_amount' => 16000.00,
                'electricity_rate' => 10.00,
                'water_share_percentage' => 50.00,
                'currency' => 'NPR',
                'rental_start_date' => '2024-11-01',
                'owner_name' => 'Ram Bahadur Shrestha',
                'owner_phone' => '+977 9841000000',
                'owner_email' => 'landlord@example.com',
                'property_address' => 'Baneshwor, Kathmandu, Nepal',
                'payment_reminder_days' => 5,
                'notes' => 'Rent due by 5th of every Nepali/English month.',
            ]
        );

        // 2. Clear existing records if re-seeding
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MonthlyRecord::truncate();
        Payment::truncate();
        AdditionalExpense::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Sample data for the past 6-7 months (May 2026 to November 2026)
        $records = [
            [
                'year' => 2026,
                'month' => 5,
                'rent_amount' => 16000.00,
                'prev_reading' => 2450.00,
                'curr_reading' => 2520.00, // 70 units = 700
                'water_bill' => 800.00,    // share = 400
                'is_historical' => true,
                'is_verified' => true,
                'notes' => 'May 2026 - fully settled',
                'payments' => [
                    ['date' => '2026-05-04', 'amount' => 17100.00, 'method' => 'eSewa', 'ref' => 'ESEWA-MAY-9921', 'notes' => 'Full rent + utilities'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 6,
                'rent_amount' => 16000.00,
                'prev_reading' => 2520.00,
                'curr_reading' => 2605.00, // 85 units = 850
                'water_bill' => 900.00,    // share = 450
                'is_historical' => true,
                'is_verified' => true,
                'notes' => 'June 2026 - paid in two installments',
                'payments' => [
                    ['date' => '2026-06-03', 'amount' => 10000.00, 'method' => 'Bank Transfer', 'ref' => 'NABIL-TXN-6612', 'notes' => '1st installment'],
                    ['date' => '2026-06-18', 'amount' => 7300.00, 'method' => 'Khalti', 'ref' => 'KHLT-TXN-8812', 'notes' => '2nd installment (cleared)'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 7,
                'rent_amount' => 16000.00,
                'prev_reading' => 2605.00,
                'curr_reading' => 2700.00, // 95 units = 950
                'water_bill' => 1000.00,   // share = 500
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'July 2026 summer consumption',
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
                'rent_amount' => 16000.00,
                'prev_reading' => 2700.00,
                'curr_reading' => 2790.00, // 90 units = 900
                'water_bill' => 850.00,    // share = 425
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'August 2026',
                'payments' => [
                    ['date' => '2026-08-04', 'amount' => 17325.00, 'method' => 'Cash', 'ref' => null, 'notes' => 'Handed over in cash to owner'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 9,
                'rent_amount' => 16000.00,
                'prev_reading' => 2790.00,
                'curr_reading' => 2885.00, // 95 units = 950
                'water_bill' => 920.00,    // share = 460
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'September 2026',
                'payments' => [
                    ['date' => '2026-09-05', 'amount' => 17410.00, 'method' => 'eSewa', 'ref' => 'ESEWA-SEP-4519', 'notes' => 'Paid on time'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 10,
                'rent_amount' => 16000.00,
                'prev_reading' => 2885.00,
                'curr_reading' => 2980.00, // 95 units = 950
                'water_bill' => 900.00,    // share = 450
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'October 2026 (Dashain month) - partially paid',
                'payments' => [
                    ['date' => '2026-10-06', 'amount' => 10000.00, 'method' => 'eSewa', 'ref' => 'ESEWA-OCT-1120', 'notes' => 'Partial payment before holidays'],
                ],
            ],
            [
                'year' => 2026,
                'month' => 11,
                'rent_amount' => 16000.00,
                'prev_reading' => 2980.00,
                'curr_reading' => 3065.00, // 85 units = 850
                'water_bill' => 860.00,    // share = 430
                'is_historical' => false,
                'is_verified' => true,
                'notes' => 'November 2026 (Current month)',
                'expenses' => [
                    ['title' => 'Waste Management', 'amount' => 350.00, 'category' => 'Waste'],
                ],
                'payments' => [], // Unpaid yet!
            ],
        ];

        foreach ($records as $item) {
            $recordDate = Carbon::createFromDate($item['year'], $item['month'], 1)->toDateString();

            $record = MonthlyRecord::create([
                'year' => $item['year'],
                'month' => $item['month'],
                'record_date' => $recordDate,
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
                    $record->additionalExpenses()->create($exp);
                }
            }

            if (!empty($item['payments'])) {
                foreach ($item['payments'] as $pay) {
                    $record->payments()->create([
                        'payment_date' => $pay['date'],
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
