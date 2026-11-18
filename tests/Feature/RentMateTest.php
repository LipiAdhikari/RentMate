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
}
