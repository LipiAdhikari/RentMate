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
        Schema::table('monthly_records', function (Blueprint $table) {
            $table->unsignedSmallInteger('bs_year')->nullable()->after('month');
            $table->unsignedTinyInteger('bs_month')->nullable()->after('bs_year');
            $table->string('nepali_month_name', 100)->nullable()->after('bs_month');
            $table->date('due_date')->nullable()->after('record_date');
            $table->date('cleared_at')->nullable()->after('status');
            $table->text('settlement_notes')->nullable()->after('cleared_at');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('bs_payment_date', 50)->nullable()->after('payment_date');
            $table->integer('days_late')->default(0)->after('amount');
            $table->boolean('cleared_full_month')->default(false)->after('days_late');
        });

        Schema::table('rental_settings', function (Blueprint $table) {
            $table->string('calendar_preference', 20)->default('both')->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('monthly_records', function (Blueprint $table) {
            $table->dropColumn(['bs_year', 'bs_month', 'nepali_month_name', 'due_date', 'cleared_at', 'settlement_notes']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['bs_payment_date', 'days_late', 'cleared_full_month']);
        });

        Schema::table('rental_settings', function (Blueprint $table) {
            $table->dropColumn(['calendar_preference']);
        });
    }
};
