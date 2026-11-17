<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('monthly_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('record_date');
            $table->decimal('rent_amount', 10, 2)->default(0.00);
            
            // Electricity Tracker
            $table->decimal('prev_electricity_reading', 10, 2)->default(0.00);
            $table->decimal('curr_electricity_reading', 10, 2)->default(0.00);
            $table->decimal('electricity_units', 10, 2)->default(0.00);
            $table->decimal('electricity_rate', 10, 2)->default(10.00);
            $table->decimal('electricity_amount', 10, 2)->default(0.00);
            
            // Water Bill Tracker
            $table->decimal('total_water_bill', 10, 2)->default(0.00);
            $table->decimal('tenant_water_percentage', 5, 2)->default(50.00);
            $table->decimal('water_amount', 10, 2)->default(0.00);
            
            // Additional Charges & Totals
            $table->decimal('other_charges', 10, 2)->default(0.00);
            $table->decimal('total_expenses', 10, 2)->default(0.00);
            $table->decimal('total_paid', 10, 2)->default(0.00);
            $table->decimal('balance', 10, 2)->default(0.00);
            
            $table->enum('status', ['unpaid', 'partially_paid', 'paid', 'overpaid'])->default('unpaid');
            $table->boolean('is_historical')->default(false);
            $table->boolean('is_verified')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['year', 'month']);
            $table->index('status');
            $table->index('record_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_records');
    }
};
