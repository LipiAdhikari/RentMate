<?php

namespace Tests\Feature;

use App\Models\AdditionalExpense;
use App\Models\MonthlyRecord;
use App\Models\Payment;
use App\Models\RentalSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RentMateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        RentalSetting::getActive();
    }

    /** Scenario 1, 2, 3: Create monthly rent record, calculate electricity and 50/50 water bill */
    public function test_can_create_monthly_record_with_automatic_calculations(): void
    {
        // Month: December 2025
        // Rent: 16000
        // Prev Reading: 1000, Curr Reading: 1100 => 100 units @ 10 = 1000
        // Water Bill: 800 => 50% = 400
        // Total expected: 16000 + 1000 + 400 = 17400
        $response = $this->post(route('monthly-records.store'), [
            'year' => 2025,
            'month' => 12,
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 1000.00,
            'curr_electricity_reading' => 1100.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 800.00,
            'tenant_water_percentage' => 50.00,
            'notes' => 'Test monthly record',
        ]);

        $response->assertRedirect();

        $record = MonthlyRecord::where('year', 2025)->where('month', 12)->first();
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
            'year' => 2025,
            'month' => 8,
            'rent_amount' => 16500.00,
            'prev_electricity_reading' => 500.00,
            'curr_electricity_reading' => 600.00, // 100 units
            'electricity_rate' => 10.00,
            'total_water_bill' => 1200.00, // 600
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

    /** Scenario 10: Add historical records with verification status */
    public function test_can_add_historical_record_with_verification_flag(): void
    {
        $response = $this->post(route('historical.store'), [
            'year' => 2024,
            'month' => 11,
            'rent_amount' => 15000.00,
            'prev_electricity_reading' => 0,
            'curr_electricity_reading' => 0,
            'electricity_rate' => 10.00,
            'total_water_bill' => 0,
            'tenant_water_percentage' => 50.00,
            'is_verified' => 0, // Estimated / incomplete
            'notes' => 'Estimated initial move-in month',
        ]);

        $response->assertRedirect(route('historical.index'));

        $record = MonthlyRecord::where('year', 2024)->where('month', 11)->first();
        $this->assertNotNull($record);
        $this->assertTrue($record->is_historical);
        $this->assertFalse($record->is_verified);
    }

    /** Scenario 11: Verify dashboard dynamic calculations */
    public function test_dashboard_calculations_match_database(): void
    {
        $record = MonthlyRecord::create([
            'year' => 2025,
            'month' => 7,
            'record_date' => '2025-07-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 100.00,
            'curr_electricity_reading' => 200.00, // 1000
            'electricity_rate' => 10.00,
            'total_water_bill' => 1000.00, // 500
            'tenant_water_percentage' => 50.00,
        ]);
        $record->payments()->create([
            'payment_date' => '2025-07-05',
            'amount' => 10000.00,
            'payment_method' => 'eSewa',
        ]);
        $record->recalculate(true);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
        $response->assertViewHas('allTimeExpenses', 17500.00);
        $response->assertViewHas('allTimePaid', 10000.00);
        $response->assertViewHas('allTimeBalance', 7500.00);
    }

    /** Scenario 12: Rate preservation guarantee (updating settings does NOT alter historical records) */
    public function test_settings_update_does_not_alter_existing_records(): void
    {
        $record = MonthlyRecord::create([
            'year' => 2025,
            'month' => 9,
            'record_date' => '2025-09-01',
            'rent_amount' => 16000.00,
            'prev_electricity_reading' => 100.00,
            'curr_electricity_reading' => 200.00,
            'electricity_rate' => 10.00,
            'total_water_bill' => 600.00,
            'tenant_water_percentage' => 50.00,
        ]);
        $record->recalculate(true);

        $initialTotal = $record->total_expenses;

        // Change settings to rent 20000 and electricity rate 15
        $this->put(route('settings.update'), [
            'rent_amount' => 20000.00,
            'electricity_rate' => 15.00,
            'water_share_percentage' => 50.00,
            'currency' => 'NPR',
            'payment_reminder_days' => 5,
        ]);

        // Verify existing record is unchanged
        $record->refresh();
        $this->assertEquals(16000.00, $record->rent_amount);
        $this->assertEquals(10.00, $record->electricity_rate);
        $this->assertEquals($initialTotal, $record->total_expenses);
    }

    /** Export and Route smoke tests */
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
    }
}
