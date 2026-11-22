<?php

namespace Tests\Feature;

use App\Models\ElectricityBill;
use App\Models\ElectricityBillAllocation;
use App\Models\ElectricityPayment;
use App\Models\MonthlyRecord;
use App\Models\RentalSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FlexibleElectricityBillingTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected RentalSetting $setting;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'email' => 'tenant.test@rentmate.com',
            'name' => 'Test Tenant',
            'password' => bcrypt('password123'),
        ]);

        $this->setting = RentalSetting::create([
            'user_id' => $this->user->id,
            'rent_amount' => 16000.00,
            'electricity_rate' => 10.00,
            'water_share_percentage' => 50.00,
            'currency' => 'NPR',
        ]);
    }

    /** 1. Scenario: One-month electricity bill */
    public function test_one_month_electricity_bill(): void
    {
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-09-30',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 200.00,
            'rate_per_unit' => 10.00,
            'notes' => 'September single month bill',
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill = ElectricityBill::forUser($this->user->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals('2026-09-01', $bill->billing_period_start->format('Y-m-d'));
        $this->assertEquals('2026-09-30', $bill->billing_period_end->format('Y-m-d'));
        $this->assertEquals(100.00, $bill->units_consumed);
        $this->assertEquals(1000.00, $bill->total_amount);
        $this->assertEquals(0.00, $bill->paid_amount);
        $this->assertEquals(1000.00, $bill->balance);
        $this->assertEquals('unpaid', $bill->status);
        $this->assertEquals(1, $bill->months_count);
        $this->assertEquals('September 2026', $bill->formatted_period);
    }

    /** 2. Scenario: Two-month electricity bill */
    public function test_two_month_electricity_bill(): void
    {
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-10-31',
            'previous_meter_reading' => 1000.00,
            'current_meter_reading' => 1250.00,
            'rate_per_unit' => 10.00,
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill = ElectricityBill::forUser($this->user->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(250.00, $bill->units_consumed);
        $this->assertEquals(2500.00, $bill->total_amount);
        $this->assertEquals(2, $bill->months_count);
        $this->assertEquals('Sep 2026 – Oct 2026', $bill->formatted_period);
    }

    /** 3. Scenario: Four-month electricity bill */
    public function test_four_month_electricity_bill(): void
    {
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 1250.00,
            'current_meter_reading' => 1650.00,
            'rate_per_unit' => 10.00,
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill = ElectricityBill::forUser($this->user->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(400.00, $bill->units_consumed);
        $this->assertEquals(4000.00, $bill->total_amount);
        $this->assertEquals(4, $bill->months_count);
        $this->assertEquals('Sep 2026 – Dec 2026', $bill->formatted_period);
    }

    /** 4. Scenario: Five-month electricity bill */
    public function test_five_month_electricity_bill(): void
    {
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-01-01',
            'billing_period_end' => '2026-05-31',
            'previous_meter_reading' => 500.00,
            'current_meter_reading' => 1100.00,
            'rate_per_unit' => 12.00,
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill = ElectricityBill::forUser($this->user->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals(600.00, $bill->units_consumed);
        $this->assertEquals(7200.00, $bill->total_amount);
        $this->assertEquals(5, $bill->months_count);
        $this->assertEquals('Jan 2026 – May 2026', $bill->formatted_period);
    }

    /** 5. Scenario: Partial electricity payment */
    public function test_partial_electricity_payment(): void
    {
        $bill = ElectricityBill::create([
            'user_id' => $this->user->id,
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 1000.00,
            'current_meter_reading' => 1400.00,
            'units_consumed' => 400.00,
            'rate_per_unit' => 10.00,
            'total_amount' => 4000.00,
            'paid_amount' => 0.00,
            'balance' => 4000.00,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->user)->post(route('electricity.bills.payments.store', $bill), [
            'payment_date' => '2026-10-15',
            'amount' => 1500.00,
            'payment_method' => 'eSewa',
            'transaction_reference' => 'ESEWA-123',
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill->refresh();
        $this->assertEquals('partially_paid', $bill->status);
        $this->assertEquals(1500.00, $bill->paid_amount);
        $this->assertEquals(2500.00, $bill->balance);
    }

    /** 6. Scenario: Full electricity payment */
    public function test_full_electricity_payment(): void
    {
        $bill = ElectricityBill::create([
            'user_id' => $this->user->id,
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 1000.00,
            'current_meter_reading' => 1400.00,
            'units_consumed' => 400.00,
            'rate_per_unit' => 10.00,
            'total_amount' => 4000.00,
            'paid_amount' => 0.00,
            'balance' => 4000.00,
            'status' => 'unpaid',
        ]);

        $response = $this->actingAs($this->user)->post(route('electricity.bills.payments.store', $bill), [
            'payment_date' => '2026-12-20',
            'amount' => 4000.00,
            'payment_method' => 'Bank Transfer',
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals(4000.00, $bill->paid_amount);
        $this->assertEquals(0.00, $bill->balance);
    }

    /** 7. Scenario: Multiple payments against one electricity bill */
    public function test_multiple_payments_against_one_electricity_bill(): void
    {
        $bill = ElectricityBill::create([
            'user_id' => $this->user->id,
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 1000.00,
            'current_meter_reading' => 1400.00,
            'units_consumed' => 400.00,
            'rate_per_unit' => 10.00,
            'total_amount' => 4000.00,
            'paid_amount' => 0.00,
            'balance' => 4000.00,
            'status' => 'unpaid',
        ]);

        // Payment 1: NPR 1,000
        $this->actingAs($this->user)->post(route('electricity.bills.payments.store', $bill), [
            'payment_date' => '2026-10-01',
            'amount' => 1000.00,
            'payment_method' => 'Cash',
        ]);

        $bill->refresh();
        $this->assertEquals('partially_paid', $bill->status);
        $this->assertEquals(1000.00, $bill->paid_amount);
        $this->assertEquals(3000.00, $bill->balance);

        // Payment 2: NPR 1,500
        $this->actingAs($this->user)->post(route('electricity.bills.payments.store', $bill), [
            'payment_date' => '2026-11-01',
            'amount' => 1500.00,
            'payment_method' => 'Khalti',
        ]);

        $bill->refresh();
        $this->assertEquals('partially_paid', $bill->status);
        $this->assertEquals(2500.00, $bill->paid_amount);
        $this->assertEquals(1500.00, $bill->balance);

        // Payment 3: NPR 1,500
        $this->actingAs($this->user)->post(route('electricity.bills.payments.store', $bill), [
            'payment_date' => '2026-12-05',
            'amount' => 1500.00,
            'payment_method' => 'Bank Transfer',
        ]);

        $bill->refresh();
        $this->assertEquals('paid', $bill->status);
        $this->assertEquals(4000.00, $bill->paid_amount);
        $this->assertEquals(0.00, $bill->balance);
        $this->assertCount(3, $bill->payments);
    }

    /** 8. Scenario: Invalid meter readings and dates validation */
    public function test_invalid_meter_readings_and_dates_validation(): void
    {
        // Current reading lower than previous reading
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-09-30',
            'previous_meter_reading' => 500.00,
            'current_meter_reading' => 450.00, // Invalid!
            'rate_per_unit' => 10.00,
        ]);

        $response->assertSessionHasErrors('current_meter_reading');

        // End date before start date
        $responseDate = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-30',
            'billing_period_end' => '2026-09-01', // Invalid!
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 200.00,
            'rate_per_unit' => 10.00,
        ]);

        $responseDate->assertSessionHasErrors('billing_period_end');
    }

    /** 9. Scenario: Historical bills logging */
    public function test_historical_bills_logging(): void
    {
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2025-01-01',
            'billing_period_end' => '2025-06-30',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 600.00,
            'rate_per_unit' => 10.00,
            'is_historical' => true,
            'notes' => 'Old historical winter/summer settlement',
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill = ElectricityBill::forUser($this->user->id)->first();
        $this->assertTrue($bill->is_historical);
        $this->assertEquals(500.00, $bill->units_consumed);
        $this->assertEquals(5000.00, $bill->total_amount);
    }

    /** 10. Scenario: Editing electricity readings and recalculation */
    public function test_editing_electricity_readings(): void
    {
        $bill = ElectricityBill::create([
            'user_id' => $this->user->id,
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-09-30',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 200.00,
            'units_consumed' => 100.00,
            'rate_per_unit' => 10.00,
            'total_amount' => 1000.00,
            'paid_amount' => 0.00,
            'balance' => 1000.00,
            'status' => 'unpaid',
        ]);

        $bill->payments()->create([
            'user_id' => $this->user->id,
            'payment_date' => '2026-09-15',
            'amount' => 400.00,
            'payment_method' => 'Cash',
        ]);
        $bill->recalculate(true);

        // Edit current reading to 250 (units 150, total 1500)
        $response = $this->actingAs($this->user)->put(route('electricity.bills.update', $bill), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-09-30',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 250.00,
            'rate_per_unit' => 10.00,
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill->refresh();
        $this->assertEquals(150.00, $bill->units_consumed);
        $this->assertEquals(1500.00, $bill->total_amount);
        $this->assertEquals(400.00, $bill->paid_amount);
        $this->assertEquals(1100.00, $bill->balance);
        $this->assertEquals('partially_paid', $bill->status);
    }

    /** 11. Scenario: Monthly expense allocation without duplicating original bill amount */
    public function test_monthly_expense_allocation(): void
    {
        // Create 4 monthly records for Sep, Oct, Nov, Dec
        $m1 = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 9,
            'record_date' => '2026-09-01',
            'rent_amount' => 16000.00,
        ]);
        $m2 = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 10,
            'record_date' => '2026-10-01',
            'rent_amount' => 16000.00,
        ]);
        $m3 = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 11,
            'record_date' => '2026-11-01',
            'rent_amount' => 16000.00,
        ]);
        $m4 = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 12,
            'record_date' => '2026-12-01',
            'rent_amount' => 16000.00,
        ]);

        // Create a 4-month bill for NPR 4,000, allocating 1,000 to each month
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 1000.00,
            'current_meter_reading' => 1400.00, // 400 units * 10 = NPR 4,000
            'rate_per_unit' => 10.00,
            'allocations' => [
                ['monthly_record_id' => $m1->id, 'allocated_amount' => 1000.00],
                ['monthly_record_id' => $m2->id, 'allocated_amount' => 1000.00],
                ['monthly_record_id' => $m3->id, 'allocated_amount' => 1000.00],
                ['monthly_record_id' => $m4->id, 'allocated_amount' => 1000.00],
            ],
        ]);

        $response->assertRedirect(route('electricity.index'));

        $bill = ElectricityBill::forUser($this->user->id)->first();
        $this->assertEquals(4000.00, $bill->total_amount);
        $this->assertEquals(4000.00, $bill->allocatedTotal());
        $this->assertEquals(0.00, $bill->unallocatedAmount());

        // Check that each monthly record has 1,000 electricity amount
        $m1->refresh();
        $m2->refresh();
        $m3->refresh();
        $m4->refresh();

        $this->assertEquals(1000.00, $m1->electricity_amount);
        $this->assertEquals(1000.00, $m2->electricity_amount);
        $this->assertEquals(1000.00, $m3->electricity_amount);
        $this->assertEquals(1000.00, $m4->electricity_amount);

        // Sum across all 4 months is exactly 4,000, NEVER 16,000!
        $totalMonthlyElec = $m1->electricity_amount + $m2->electricity_amount + $m3->electricity_amount + $m4->electricity_amount;
        $this->assertEquals(4000.00, $totalMonthlyElec);

        // Validation test: allocating more than the bill is rejected
        $overResponse = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2027-01-01',
            'billing_period_end' => '2027-02-28',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 200.00, // 100 units * 10 = 1,000
            'rate_per_unit' => 10.00,
            'allocations' => [
                ['monthly_record_id' => $m1->id, 'allocated_amount' => 1500.00], // 1,500 > 1,000!
            ],
        ]);
        $overResponse->assertSessionHasErrors('allocations');
    }

    /** 12. Scenario: Dashboard and reports calculation accuracy */
    public function test_dashboard_calculation_accuracy_and_rent_separation(): void
    {
        $mRec = MonthlyRecord::create([
            'user_id' => $this->user->id,
            'year' => 2026,
            'month' => 9,
            'record_date' => '2026-09-01',
            'rent_amount' => 16000.00,
            'status' => 'unpaid',
        ]);
        $mRec->recalculate(true);

        $bill = ElectricityBill::create([
            'user_id' => $this->user->id,
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-10-31',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 300.00,
            'units_consumed' => 200.00,
            'rate_per_unit' => 10.00,
            'total_amount' => 2000.00,
            'paid_amount' => 0.00,
            'balance' => 2000.00,
            'status' => 'unpaid',
        ]);

        // Pay monthly rent
        $mRec->payments()->create([
            'user_id' => $this->user->id,
            'payment_date' => '2026-09-05',
            'amount' => 16000.00,
            'payment_method' => 'eSewa',
        ]);
        $mRec->recalculate(true);

        // Rent is cleared, but electricity bill remains UNPAID!
        $this->assertEquals('paid', $mRec->status);
        $bill->refresh();
        $this->assertEquals('unpaid', $bill->status);
        $this->assertEquals(2000.00, $bill->balance);

        // Check electricity dashboard view
        $response = $this->actingAs($this->user)->get(route('electricity.index'));
        $response->assertOk();
        $response->assertSee('2,000.00');
        $response->assertSee('Unpaid');
    }

    /** 13. Scenario: Duplicate bill prevention */
    public function test_duplicate_bill_prevention(): void
    {
        ElectricityBill::create([
            'user_id' => $this->user->id,
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 300.00,
            'units_consumed' => 200.00,
            'rate_per_unit' => 10.00,
            'total_amount' => 2000.00,
            'paid_amount' => 0.00,
            'balance' => 2000.00,
            'status' => 'unpaid',
        ]);

        // Attempt exact duplicate for same user
        $response = $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 300.00,
            'current_meter_reading' => 500.00,
            'rate_per_unit' => 10.00,
        ]);

        $response->assertSessionHasErrors('billing_period_start');

        // Another user can create bill for same dates
        $otherUser = User::create([
            'email' => 'other.user@rentmate.com',
            'name' => 'Other User',
            'password' => bcrypt('password123'),
        ]);
        $otherResponse = $this->actingAs($otherUser)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-09-01',
            'billing_period_end' => '2026-12-31',
            'previous_meter_reading' => 50.00,
            'current_meter_reading' => 150.00,
            'rate_per_unit' => 10.00,
        ]);
        $otherResponse->assertRedirect(route('electricity.index'));
    }

    /** 14. Scenario: Changing electricity rates for future bills */
    public function test_changing_electricity_rates_for_future_bills(): void
    {
        // First bill at rate 10.00
        $bill1 = ElectricityBill::create([
            'user_id' => $this->user->id,
            'billing_period_start' => '2026-01-01',
            'billing_period_end' => '2026-03-31',
            'previous_meter_reading' => 100.00,
            'current_meter_reading' => 300.00,
            'units_consumed' => 200.00,
            'rate_per_unit' => 10.00,
            'total_amount' => 2000.00,
            'paid_amount' => 2000.00,
            'balance' => 0.00,
            'status' => 'paid',
        ]);

        // Rate increases in settings to 12.50
        $this->setting->update(['electricity_rate' => 12.50]);

        // Second bill created at new rate 12.50
        $this->actingAs($this->user)->post(route('electricity.bills.store'), [
            'billing_period_start' => '2026-04-01',
            'billing_period_end' => '2026-06-30',
            'previous_meter_reading' => 300.00,
            'current_meter_reading' => 500.00,
            'rate_per_unit' => 12.50,
        ]);

        $bill2 = ElectricityBill::forUser($this->user->id)->whereDate('billing_period_start', '2026-04-01')->first();
        $this->assertEquals(12.50, $bill2->rate_per_unit);
        $this->assertEquals(2500.00, $bill2->total_amount);

        // Verify Bill 1 rate and amount were preserved intact!
        $bill1->refresh();
        $this->assertEquals(10.00, $bill1->rate_per_unit);
        $this->assertEquals(2000.00, $bill1->total_amount);
    }
}
