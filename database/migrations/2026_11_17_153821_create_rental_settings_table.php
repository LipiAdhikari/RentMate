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
        Schema::create('rental_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('rent_amount', 10, 2)->default(16000.00);
            $table->decimal('electricity_rate', 10, 2)->default(10.00);
            $table->decimal('water_share_percentage', 5, 2)->default(50.00);
            $table->string('currency', 10)->default('NPR');
            $table->date('rental_start_date')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('owner_email')->nullable();
            $table->string('property_address')->nullable();
            $table->unsignedSmallInteger('payment_reminder_days')->default(5);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_settings');
    }
};
