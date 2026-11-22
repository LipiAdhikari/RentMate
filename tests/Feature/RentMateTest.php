<?php

namespace Tests\Feature;

use App\Models\AdditionalExpense;
use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use App\Models\User;
use App\Services\NepaliCalendar;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RentMateTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a test user
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test@rentmate.com',
            'password' => Hash::make('password123'),
        ]);

        RentalSetting::create([
            'user_id' => $this->user->id,
            'rent_amount' => 16000.00,
            'electricity_rate' => 10.00,
            'water_share_percentage' => 50.00,
            'currency' => 'NPR',
            'rental_start_date' => '2024-11-01',
            'owner_name' => 'House Owner',
            'property_address' => 'Kathmandu, Nepal',
            'payment_reminder_days' => 5,
        ]);

        $this->actingAs($this->user);
    }

    /** Scenario: January rent paid on February 15 (15 days late) auto-detected as 1 month rent cleared */
    public function test_late_payment_on_feb_15_for_january_rent_auto_detected_as_one_month_cleared(): void
    {
        // 1. Create January 2026 record (Due date: 2026-01-31)
        $january = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 1,
            'bs_year' => 2082,
            'bs_month' => 10, // Magh
            'nepali_month_name' => 'Magh (माघ)',
            'record_date' => '2026-01-01',
            'due_date' => '2026-01-31',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 100.00,
            'curr_electricity_reading' => 100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 0.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $january->recalculate(true);

        $this->assertEquals('unpaid', $january->status);
        $this->assertEquals(16000.00, $january->balance);

        // 2. Pay rent on February 15, 2026 (15 days after due date)
        $response = $this->post(route('payments.store'), [
            'monthly_record_id' => $january->id,
            'amount' => 16000.00,
            'payment_date' => '2026-02-15',
            'payment_method' => 'eSewa',
            'transaction_reference' => 'ESEWA-FEB15-LATE',
            'notes' => 'Settled January rent late',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // 3. Verify system automatically detected 1 month rent cleared and 15 days late
        $january->refresh();
        $payment = $january->payments()->first();

        $this->assertNotNull($payment);
        $this->assertEquals(15, $payment->days_late);
        $this->assertEquals('paid', $january->status);
        $this->assertEquals(0.00, $january->balance);
        $this->assertEquals('2026-02-15', $january->cleared_at->toDateString());
        $this->assertStringContainsString('1 Month Rent fully paid on Feb 15, 2026 (15 days after due date)', $january->settlement_notes);

        // 4. Verify Nepali date conversion
        $this->assertNotNull($payment->bs_payment_date);
    }

    /** Scenario: Auto-allocation of payment to oldest unpaid month */
    public function test_auto_allocation_applies_payment_to_oldest_unpaid_month(): void
    {
        // Month 1: Unpaid
        $jan = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 1,
            'record_date' => '2026-01-01',
            'rent_amount' => 16000.00,
            'electricity_rate' => 10.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $jan->recalculate(true);

        // Month 2: Unpaid
        $feb = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 2,
            'record_date' => '2026-02-01',
            'rent_amount' => 16000.00,
            'electricity_rate' => 10.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $feb->recalculate(true);

        // Pay with monthly_record_id = 'auto'
        $this->post(route('payments.store'), [
            'monthly_record_id' => 'auto',
            'amount' => 16000.00,
            'payment_date' => '2026-02-15',
            'payment_method' => 'Bank Transfer',
        ]);

        // January should be cleared first!
        $jan->refresh();
        $feb->refresh();

        $this->assertEquals('paid', $jan->status);
        $this->assertEquals(0.00, $jan->balance);
        $this->assertEquals('unpaid', $feb->status);
        $this->assertEquals(16000.00, $feb->balance);
    }

    /** Scenario 1, 2, 3: Create monthly rent record, calculate electricity and 50/50 water bill */
    public function test_can_create_monthly_record_with_automatic_calculations(): void
    {
        $response = $this->post(route('monthly-records.store'), [
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 12,
            'bs_year' => 2082,
            'bs_month' => 9, // Poush
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1000.00,
            'curr_electricity_reading' => 1100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
            'notes' => 'Test monthly record',
        ]);

        $response->assertRedirect();

        $record = MonthlyRecord::where('user_id', $this->user->id)->where('year', 2025)->where('month', 12)->first();
        $this->assertNotNull($record);
        $this->assertEquals(100.00, $record->electricity_units);
        $this->assertEquals(1000.00, $record->electricity_amount);
        $this->assertEquals(400.00, $record->water_amount);
        $this->assertEquals(17400.00, $record->total_expenses);
        $this->assertEquals(0.00, $record->total_paid);
        $this->assertEquals(17400.00, $record->balance);
        $this->assertEquals('unpaid', $record->status);
    }

    /** Scenario 2 (Validation): Prevents meter reading from being lower than previous reading */
    public function test_validation_rejects_current_reading_lower_than_previous(): void
    {
        $response = $this->post(route('monthly-records.store'), [
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 1,
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1500.00,
            'curr_electricity_reading' => 1400.00, // Invalid!
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
        ]);

        $response->assertSessionHasErrors(['curr_electricity_reading']);
    }

    /** Scenario 4, 5, 6, 7: Record single payment, multiple partial payments, verify balances and statuses */
    public function test_recording_multiple_partial_payments_updates_balance_and_status(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 11,
            'record_date' => '2025-11-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 100.00,
            'curr_electricity_reading' => 160.00, // 60 units * 10 = 600
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00, // 50% = 400
            'tenant_water_percentage' => 50.00,
        ]);
        $record->recalculate(true);

        $this->assertEquals('unpaid', $record->status);
        $this->assertEquals(17000.00, $record->balance);

        // First partial payment: NPR 10,000 via eSewa
        $this->post(route('payments.store'), [
            'monthly_record_id' => $record->id,
            'amount' => 10000.00,
            'payment_date' => '2025-11-05',
            'payment_method' => 'eSewa',
            'transaction_reference' => 'ESEWA-PARTIAL-1',
        ]);

        $record->refresh();
        $this->assertEquals(10000.00, $record->total_paid);
        $this->assertEquals(7000.00, $record->balance);
        $this->assertEquals('partially_paid', $record->status);

        // Second payment to clear the balance: NPR 7,000 via Bank Transfer
        $this->post(route('payments.store'), [
            'monthly_record_id' => $record->id,
            'amount' => 7000.00,
            'payment_date' => '2025-11-15',
            'payment_method' => 'Bank Transfer',
            'transaction_reference' => 'BANK-CLEAR-2',
        ]);

        $record->refresh();
        $this->assertEquals(17000.00, $record->total_paid);
        $this->assertEquals(0.00, $record->balance);
        $this->assertEquals('paid', $record->status);
        $this->assertEquals(2, $record->payments()->count());
    }

    /** Scenario 8: Overpayment handling */
    public function test_overpayment_sets_overpaid_status(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 10,
            'record_date' => '2025-10-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 0.00,
            'curr_electricity_reading' => 0.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 0.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $record->recalculate(true);

        // Pay 18,000 against 16,000 total
        $this->post(route('payments.store'), [
            'monthly_record_id' => $record->id,
            'amount' => 18000.00,
            'payment_date' => '2025-10-04',
            'payment_method' => 'Cash',
        ]);

        $record->refresh();
        $this->assertEquals(-2000.00, $record->balance);
        $this->assertEquals('overpaid', $record->status);
    }

    /** Scenario 9: Edit and delete records */
    public function test_can_edit_and_delete_record(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 8,
            'record_date' => '2025-08-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 500.00,
            'curr_electricity_reading' => 580.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 1000.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $record->recalculate(true);

        // Edit record
        $this->put(route('monthly-records.update', $record), [
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 8,
            'rent_amount' => 16500.00,
            'prev_electricity_reading' => 500.00,
            'curr_electricity_reading' => 600.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 1200.00,
            'tenant_water_percentage' => 50.00,
        ]);

        $record->refresh();
        $this->assertEquals(16500.00, $record->rent_amount);
        $this->assertEquals(100.00, $record->electricity_units);
        $this->assertEquals(1000.00, $record->electricity_amount);
        $this->assertEquals(600.00, $record->water_amount);
        $this->assertEquals(18100.00, $record->total_expenses);

        // Delete record
        $response = $this->delete(route('monthly-records.destroy', $record));
        $response->assertRedirect(route('monthly-records.index'));
        $this->assertDatabaseMissing('monthly_records', ['id' => $record->id]);
    }

    /** Nepali Calendar unit validation */
    public function test_nepali_calendar_conversion_accuracy(): void
    {
        $bs = NepaliCalendar::adToBs('2026-11-17');
        $this->assertEquals(2083, $bs['year']);
        $this->assertEquals(8, $bs['month']); // Mangsir
        $this->assertEquals('Mangsir', $bs['month_en']);
        $this->assertEquals('मंसिर', $bs['month_np']);

        // Verify BS to AD conversion accuracy
        $this->assertEquals('2026-11-18', NepaliCalendar::bsToAd(2083, 8, 2));
        $this->assertEquals('2026-11-19', NepaliCalendar::bsToAd(2083, 8, 3));
        $this->assertEquals('2026-04-14', NepaliCalendar::bsToAd(2083, 1, 1));
        $this->assertEquals('2026-04-13', NepaliCalendar::bsToAd(2082, 12, 30));

        // Full round-trip test: adToBs -> bsToAd gives original date
        $testDates = ['2026-01-15', '2026-04-14', '2026-11-18', '2026-11-19', '2027-02-10'];
        foreach ($testDates as $adDate) {
            $convertedBs = NepaliCalendar::adToBs($adDate);
            $backToAd = NepaliCalendar::bsToAd($convertedBs['year'], $convertedBs['month'], $convertedBs['day']);
            $this->assertEquals($adDate, $backToAd, "Failed round-trip for date {$adDate}");
        }
    }

    /** Smoke tests for all routes */
    public function test_all_routes_and_exports_accessible(): void
    {
        $this->get(route('dashboard'))->assertOk();
        $this->get(route('monthly-records.index'))->assertOk();
        $this->get(route('electricity.index'))->assertOk();
        $this->get(route('water.index'))->assertOk();
        $this->get(route('payments.index'))->assertOk();
        $this->get(route('historical.index'))->assertOk();
        $this->get(route('reports.index'))->assertOk();
        $this->get(route('reports.export.csv'))->assertOk();
        $this->get(route('settings.index'))->assertOk();
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
    }

    /** Scenario: Account data isolation - user cannot access another user's records */
    public function test_user_cannot_access_another_users_records(): void
    {
        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@rentmate.com',
            'password' => Hash::make('password123'),
        ]);

        $otherRecord = MonthlyRecord::create([
            'user_id' => $otherUser->id,
            'year' => 2025,
            'month' => 5,
            'record_date' => '2025-05-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1000.00,
            'curr_electricity_reading' => 1100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
            'is_historical' => false,
            'is_verified' => true,
        ]);

        // Try to access other user's record - should be blocked
        $response = $this->get(route('monthly-records.show', $otherRecord));
        $response->assertStatus(403);
    }

    /** Scenario: Monthly record duplicate month prevention */
    public function test_duplicate_month_prevention(): void
    {
        MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 5,
            'record_date' => '2025-05-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1000.00,
            'curr_electricity_reading' => 1100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
            'is_historical' => false,
            'is_verified' => true,
        ]);

        $response = $this->post(route('monthly-records.store'), [
            'year' => 2025,
            'month' => 5,
            'bs_year' => 2082,
            'bs_month' => 2,
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1000.00,
            'curr_electricity_reading' => 1100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
        ]);

        $response->assertSessionHasErrors(['year']);
    }

    /** Scenario: Historical rate preservation - changing settings doesn't affect existing records */
    public function test_historical_rate_preservation(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 5,
            'record_date' => '2025-05-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1000.00,
            'curr_electricity_reading' => 1100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
            'is_historical' => false,
            'is_verified' => true,
        ]);
        $record->recalculate(true);

        $originalRent = $record->rent_amount;
        $originalElecRate = $record->electricity_rate;
        $originalWaterPct = $record->tenant_water_percentage;

        // Change settings
        $settings = RentalSetting::forUser($this->user->id);
        $settings->update([
            'rent_amount' => 20000.00,
            'electricity_rate' => 15.00,
            'water_share_percentage' => 60.00,
        ]);

        // Refresh record - rates should be preserved
        $record->refresh();
        $this->assertEquals($originalRent, $record->rent_amount);
        $this->assertEquals($originalElecRate, $record->electricity_rate);
        $this->assertEquals($originalWaterPct, $record->tenant_water_percentage);
    }

    /** Scenario: Dashboard calculations accuracy */
    public function test_dashboard_calculations_accuracy(): void
    {
        $record1 = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 5,
            'record_date' => '2025-05-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1000.00,
            'curr_electricity_reading' => 1100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
            'is_historical' => false,
            'is_verified' => true,
        ]);
        $record1->recalculate(true);

        $record2 = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 6,
            'record_date' => '2025-06-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1100.00,
            'curr_electricity_reading' => 1200.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 900.00,
            'tenant_water_percentage' => 50.00,
            'is_historical' => false,
            'is_verified' => true,
        ]);
        $record2->recalculate(true);

        $this->get(route('dashboard'))->assertOk();

        // Verify calculations
        $this->assertEquals(32000.00, $record1->rent_amount + $record2->rent_amount);
        $this->assertEquals(1000.00 + 1000.00, $record1->electricity_amount + $record2->electricity_amount);
        $this->assertEquals(400.00 + 450.00, $record1->water_amount + $record2->water_amount);
    }

    /** Scenario: Historical rate preservation when settings change */
    public function test_settings_change_does_not_affect_historical_records(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2025,
            'month' => 3,
            'record_date' => '2025-03-01',
            'rent_amount' => 15000.00,
            'prev_electricity_reading' => 900.00,
            'curr_electricity_reading' => 1000.00,
            'electricity_rate' => 8.00,
            'total_water_bill' => 600.00,
            'tenant_water_percentage' => 40.00,
            'is_historical' => true,
            'is_verified' => true,
        ]);
        $record->recalculate(true);

        $originalRent = $record->rent_amount;
        $originalElecRate = $record->electricity_rate;
        $originalWaterPct = $record->tenant_water_percentage;

        // Change settings to different values
        $settings = RentalSetting::forUser($this->user->id);
        $settings->update([
            'rent_amount' => 25000.00,
            'electricity_rate' => 20.00,
            'water_share_percentage' => 75.00,
        ]);

        $record->refresh();
        $this->assertEquals(15000.00, $record->rent_amount);
        $this->assertEquals(8.00, $record->electricity_rate);
        $this->assertEquals(40.00, $record->tenant_water_percentage);
    }

    /**
     * Complete User Workflow as defined in finalcheck.md Section 3:
     * - Config: Rent 16,000, Electricity 10/unit, Water 50%
     * - November record: Prev 1,250, Curr 1,380 -> 130 units -> 1,300 NPR
     * - Water bill: 600 -> Tenant share 300 NPR
     * - Total Expense: 17,600 NPR
     * - 3 Payments: 10,000 + 5,000 + 2,600 = 17,600 NPR
     * - Fully Paid, Balance 0
     * - Partial payment month, overpayment, edit payment, delete payment
     */
    public function test_complete_user_workflow_scenario_from_finalcheck_spec(): void
    {
        // 1. Create November record via HTTP POST
        $createResponse = $this->post(route('monthly-records.store'), [
            'year' => 2026,
            'month' => 11,
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1250.00,
            'curr_electricity_reading' => 1380.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 600.00,
            'tenant_water_percentage' => 50.00,
            'notes' => 'November 2026 rent',
        ]);
        $createResponse->assertRedirect();

        $novRecord = MonthlyRecord::forUser($this->user->id)
            ->where('year', 2026)
            ->where('month', 11)
            ->first();

        $this->assertNotNull($novRecord);
        $this->assertEquals(130.00, $novRecord->electricity_units);
        $this->assertEquals(1300.00, $novRecord->electricity_amount);
        $this->assertEquals(300.00, $novRecord->water_amount);
        $this->assertEquals(17600.00, $novRecord->total_expenses);
        $this->assertEquals(0.00, $novRecord->total_paid);
        $this->assertEquals(17600.00, $novRecord->balance);
        $this->assertEquals('unpaid', $novRecord->status);

        // 2. Record 3 sequential payments: 10,000, 5,000, 2,600
        // Payment 1: NPR 10,000
        $this->post(route('payments.store'), [
            'monthly_record_id' => $novRecord->id,
            'amount' => 10000.00,
            'payment_date' => '2026-11-05',
            'payment_method' => 'Bank Transfer',
            'transaction_reference' => 'TXN-NOV-1',
        ])->assertRedirect();

        $novRecord->refresh();
        $this->assertEquals(10000.00, $novRecord->total_paid);
        $this->assertEquals(7600.00, $novRecord->balance);
        $this->assertEquals('partially_paid', $novRecord->status);

        // Payment 2: NPR 5,000
        $this->post(route('payments.store'), [
            'monthly_record_id' => $novRecord->id,
            'amount' => 5000.00,
            'payment_date' => '2026-11-12',
            'payment_method' => 'eSewa',
            'transaction_reference' => 'TXN-NOV-2',
        ])->assertRedirect();

        $novRecord->refresh();
        $this->assertEquals(15000.00, $novRecord->total_paid);
        $this->assertEquals(2600.00, $novRecord->balance);
        $this->assertEquals('partially_paid', $novRecord->status);

        // Payment 3: NPR 2,600
        $this->post(route('payments.store'), [
            'monthly_record_id' => $novRecord->id,
            'amount' => 2600.00,
            'payment_date' => '2026-11-20',
            'payment_method' => 'Cash',
            'transaction_reference' => 'TXN-NOV-3',
        ])->assertRedirect();

        $novRecord->refresh();
        $this->assertEquals(17600.00, $novRecord->total_paid);
        $this->assertEquals(0.00, $novRecord->balance);
        $this->assertEquals('paid', $novRecord->status);
        $this->assertEquals(3, $novRecord->payments()->count());

        // 3. Test Editing a payment: Change Payment 3 from 2,600 to 2,000 (creates partial payment)
        $payment3 = $novRecord->payments()->where('transaction_reference', 'TXN-NOV-3')->first();
        $this->put(route('payments.update', $payment3), [
            'payment_date' => '2026-11-20',
            'amount' => 2000.00,
            'payment_method' => 'Cash',
            'transaction_reference' => 'TXN-NOV-3-EDITED',
        ])->assertRedirect();

        $novRecord->refresh();
        $this->assertEquals(17000.00, $novRecord->total_paid);
        $this->assertEquals(600.00, $novRecord->balance);
        $this->assertEquals('partially_paid', $novRecord->status);

        // 4. Test Deleting a payment: Delete Payment 3
        $this->delete(route('payments.destroy', $payment3))->assertRedirect();

        $novRecord->refresh();
        $this->assertEquals(15000.00, $novRecord->total_paid);
        $this->assertEquals(2600.00, $novRecord->balance);
        $this->assertEquals('partially_paid', $novRecord->status);
        $this->assertEquals(2, $novRecord->payments()->count());
    }

    /** Scenario: Electricity readings update endpoint */
    public function test_electricity_reading_update_endpoint(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 4,
            'record_date' => '2026-04-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 500.00,
            'curr_electricity_reading' => 600.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $record->recalculate(true);

        $this->assertEquals(100.00, $record->electricity_units);
        $this->assertEquals(1000.00, $record->electricity_amount);

        // Update electricity reading via controller endpoint
        $response = $this->put(route('electricity.update-reading', $record), [
            'prev_electricity_reading' => 500.00,
            'curr_electricity_reading' => 650.00,
            'electricity_rate' => 11.00,
        ]);

        $response->assertRedirect();
        $record->refresh();
        $this->assertEquals(150.00, $record->electricity_units);
        $this->assertEquals(1650.00, $record->electricity_amount);
        $this->assertEquals(18050.00, $record->total_expenses);
    }

    /** Scenario: Water bill update endpoint */
    public function test_water_bill_update_endpoint(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 6,
            'record_date' => '2026-06-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 100.00,
            'curr_electricity_reading' => 100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 1000.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $record->recalculate(true);

        $this->assertEquals(500.00, $record->water_amount);

        // Update water bill via controller endpoint
        $response = $this->put(route('water.update-bill', $record), [
            'total_water_bill' => 1400.00,
            'tenant_water_percentage' => 40.00,
        ]);

        $response->assertRedirect();
        $record->refresh();
        $this->assertEquals(1400.00, $record->total_water_bill);
        $this->assertEquals(40.00, $record->tenant_water_percentage);
        $this->assertEquals(560.00, $record->water_amount);
        $this->assertEquals(840.00, $record->owner_water_share);
        $this->assertEquals(1400.00, $record->water_amount + $record->owner_water_share);
    }

    /** Scenario: Historical records add verified and estimated */
    public function test_historical_record_creation_verified_and_estimated(): void
    {
        // Add verified record
        $response1 = $this->post(route('historical.store'), [
            'year' => 2023,
            'month' => 7,
            'rent_amount' => 14000.00,
            'prev_electricity_reading' => 0,
            'curr_electricity_reading' => 50,
            'electricity_rate' => 10.00,
            'total_water_bill' => 500.00,
            'tenant_water_percentage' => 50.00,
            'is_verified' => 1,
            'payment_amount' => 14750.00,
            'payment_method' => 'Cash',
        ]);
        $response1->assertRedirect(route('historical.index'));

        $rec1 = MonthlyRecord::forUser($this->user->id)->where('year', 2023)->where('month', 7)->first();
        $this->assertNotNull($rec1);
        $this->assertTrue((bool) $rec1->is_historical);
        $this->assertTrue((bool) $rec1->is_verified);
        $this->assertEquals('paid', $rec1->status);

        // Add estimated record without fabricating utility readings
        $response2 = $this->post(route('historical.store'), [
            'year' => 2023,
            'month' => 8,
            'rent_amount' => 14000.00,
            'electricity_rate' => 10.00,
            'tenant_water_percentage' => 50.00,
            'is_verified' => 0,
            'notes' => 'Electricity & water bills unknown for this month',
        ]);
        $response2->assertRedirect(route('historical.index'));

        $rec2 = MonthlyRecord::forUser($this->user->id)->where('year', 2023)->where('month', 8)->first();
        $this->assertNotNull($rec2);
        $this->assertTrue((bool) $rec2->is_historical);
        $this->assertFalse((bool) $rec2->is_verified);
        $this->assertEquals(14000.00, $rec2->total_expenses);
    }

    /** Scenario: Reports PDF export returns valid downloadable PDF */
    public function test_reports_pdf_export_downloads_successfully(): void
    {
        MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 1,
            'record_date' => '2026-01-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 100.00,
            'curr_electricity_reading' => 150.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
        ])->recalculate(true);

        $response = $this->get(route('reports.export.pdf'));
        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** Scenario: User Registration and Profile update */
    public function test_user_registration_creates_account_and_default_settings(): void
    {
        $this->post(route('logout'));

        $response = $this->post(route('register.store'), [
            'name' => 'New Tenant',
            'email' => 'newtenant@example.com',
            'password' => 'SecurePass123!',
            'password_confirmation' => 'SecurePass123!',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $newUser = User::where('email', 'newtenant@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertNotNull($newUser->rentalSetting);
        $this->assertEquals(16000.00, $newUser->rentalSetting->rent_amount);

        // Update profile
        $this->put(route('profile.update'), [
            'name' => 'Updated Tenant Name',
            'email' => 'newtenant@example.com',
        ])->assertRedirect(route('profile.show'));

        $newUser->refresh();
        $this->assertEquals('Updated Tenant Name', $newUser->name);
    }

    /** Scenario: Record payment using BS calendar inputs */
    public function test_record_payment_using_bs_calendar_fields(): void
    {
        $record = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 11,
            'bs_year' => 2083,
            'bs_month' => 8,
            'record_date' => '2026-11-01',
            'due_date' => '2026-11-30',
            'rent_amount' => 16000.00,
        ]);
        $record->recalculate(true);

        // Submit payment with BS year 2083, Mangsir (8), Day 3
        $response = $this->post(route('payments.store'), [
            'monthly_record_id' => $record->id,
            'amount' => 16000.00,
            'bs_year' => 2083,
            'bs_month' => 8,
            'bs_day' => 3,
            'payment_method' => 'eSewa',
            'transaction_reference' => 'BS-PAY-MANGSIR3',
        ]);

        $response->assertRedirect();
        $record->refresh();
        $payment = $record->payments()->where('transaction_reference', 'BS-PAY-MANGSIR3')->first();
        $this->assertNotNull($payment);
        $this->assertEquals('2026-11-19', $payment->payment_date->format('Y-m-d'));
        $this->assertStringContainsString('Mangsir 03, 2083', $payment->bs_payment_date);
        $this->assertEquals('paid', $record->status);
    }

    /** Scenario: Register page back button redirects to landing page and has clean name placeholder */
    public function test_register_page_back_button_points_to_landing_page(): void
    {
        $response = $this->get(route('register'));
        $response->assertOk();
        $landingUrl = route('landing');
        $response->assertSee('href="' . $landingUrl . '"', false);
        $response->assertSee('placeholder="Your Name"', false);
        $response->assertDontSee('Sajan Poudel');
    }

    /** Scenario: Login page renders proper divider structure */
    public function test_login_page_renders_clean_divider(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertSee('Or continue with');
        // Ensure hr is not relative with floating span
        $response->assertDontSee('<hr class="relative my-6">', false);
    }

    /** Scenario: Record Payment modal and Payments index page have clear Back actions */
    public function test_record_payment_and_payments_page_have_back_actions(): void
    {
        // 1. Dashboard page renders global-payment-modal with prominent Back button
        $dashResponse = $this->get(route('dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertSee('global-payment-modal');
        $dashResponse->assertSee('Back');

        // 2. Payments page has Back to Dashboard navigation
        $payResponse = $this->get(route('payments.index'));
        $payResponse->assertOk();
        $payResponse->assertSee('Back to Dashboard');
        $payResponse->assertSee(route('dashboard'));
    }
}

