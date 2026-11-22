<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create electricity_bills table
        if (!Schema::hasTable('electricity_bills')) {
            Schema::create('electricity_bills', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->date('billing_period_start');
                $table->date('billing_period_end');
                $table->decimal('previous_meter_reading', 10, 2)->default(0.00);
                $table->decimal('current_meter_reading', 10, 2)->default(0.00);
                $table->decimal('units_consumed', 10, 2)->default(0.00);
                $table->decimal('rate_per_unit', 10, 2)->default(10.00);
                $table->decimal('total_amount', 10, 2)->default(0.00);
                $table->decimal('paid_amount', 10, 2)->default(0.00);
                $table->decimal('balance', 10, 2)->default(0.00);
                $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'overpaid'])->default('unpaid');
                $table->boolean('is_historical')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index(['user_id', 'status']);
                $table->index(['user_id', 'billing_period_start', 'billing_period_end']);
            });
        }

        // 2. Create electricity_bill_allocations table
        if (!Schema::hasTable('electricity_bill_allocations')) {
            Schema::create('electricity_bill_allocations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('electricity_bill_id');
                $table->unsignedBigInteger('monthly_record_id');
                $table->decimal('allocated_amount', 10, 2)->default(0.00);
                $table->string('notes')->nullable();
                $table->timestamps();

                $table->foreign('electricity_bill_id')->references('id')->on('electricity_bills')->cascadeOnDelete();
                $table->foreign('monthly_record_id')->references('id')->on('monthly_records')->cascadeOnDelete();
                $table->unique(['electricity_bill_id', 'monthly_record_id'], 'elec_bill_alloc_unique');
            });
        }

        // 3. Create electricity_payments table
        if (!Schema::hasTable('electricity_payments')) {
            Schema::create('electricity_payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('electricity_bill_id');
                $table->unsignedBigInteger('user_id');
                $table->date('payment_date');
                $table->string('bs_payment_date')->nullable();
                $table->decimal('amount', 10, 2);
                $table->string('payment_method')->default('Cash');
                $table->string('transaction_reference')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('electricity_bill_id')->references('id')->on('electricity_bills')->cascadeOnDelete();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index(['user_id', 'payment_date']);
            });
        }

        // 4. Data Migration: Migrate existing monthly records with electricity data into electricity_bills
        try {
            $existingRecords = DB::table('monthly_records')
                ->where(function ($q) {
                    $q->where('curr_electricity_reading', '>', 0)
                      ->orWhere('electricity_amount', '>', 0);
                })
                ->get();

            foreach ($existingRecords as $rec) {
                if (!$rec->user_id) continue;

                $start = $rec->record_date ?? Carbon::createFromDate($rec->year, $rec->month, 1)->toDateString();
                $end = $rec->due_date ?? Carbon::parse($start)->endOfMonth()->toDateString();

                // Check if already migrated
                $exists = DB::table('electricity_bills')
                    ->where('user_id', $rec->user_id)
                    ->where('billing_period_start', $start)
                    ->where('billing_period_end', $end)
                    ->exists();

                if (!$exists) {
                    $units = max(0, (float)$rec->curr_electricity_reading - (float)$rec->prev_electricity_reading);
                    $rate = (float)$rec->electricity_rate > 0 ? (float)$rec->electricity_rate : 10.00;
                    $total = (float)$rec->electricity_amount > 0 ? (float)$rec->electricity_amount : round($units * $rate, 2);
                    $isPaid = in_array($rec->status, ['paid', 'overpaid']);
                    $paidAmount = $isPaid ? $total : 0.00;
                    $balance = $total - $paidAmount;
                    $status = $isPaid ? 'paid' : 'unpaid';

                    $billId = DB::table('electricity_bills')->insertGetId([
                        'user_id' => $rec->user_id,
                        'billing_period_start' => $start,
                        'billing_period_end' => $end,
                        'previous_meter_reading' => $rec->prev_electricity_reading,
                        'current_meter_reading' => $rec->curr_electricity_reading,
                        'units_consumed' => $units,
                        'rate_per_unit' => $rate,
                        'total_amount' => $total,
                        'paid_amount' => $paidAmount,
                        'balance' => $balance,
                        'status' => $status,
                        'is_historical' => (bool)$rec->is_historical,
                        'notes' => 'Migrated from ' . Carbon::parse($start)->format('F Y') . ' monthly record',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // Allocate 100% to this monthly record
                    DB::table('electricity_bill_allocations')->insert([
                        'electricity_bill_id' => $billId,
                        'monthly_record_id' => $rec->id,
                        'allocated_amount' => $total,
                        'notes' => 'Initial migration allocation',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    // If paid, create payment record
                    if ($isPaid && $total > 0) {
                        DB::table('electricity_payments')->insert([
                            'electricity_bill_id' => $billId,
                            'user_id' => $rec->user_id,
                            'payment_date' => $rec->cleared_at ?? $end,
                            'bs_payment_date' => null,
                            'amount' => $total,
                            'payment_method' => 'Bank Transfer',
                            'transaction_reference' => 'SETTLED-WITH-RENT',
                            'notes' => 'Settled with monthly rent',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {
            // Log or ignore during fresh migrations
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('electricity_payments');
        Schema::dropIfExists('electricity_bill_allocations');
        Schema::dropIfExists('electricity_bills');
    }
};
