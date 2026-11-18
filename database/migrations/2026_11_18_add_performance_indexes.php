<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indexes for monthly_records - frequently queried columns
        Schema::table('monthly_records', function (Blueprint $table) {
            $table->index(['user_id', 'year', 'month'], 'idx_monthly_records_user_year_month');
            $table->index(['user_id', 'record_date'], 'idx_monthly_records_user_record_date');
            $table->index(['user_id', 'status'], 'idx_monthly_records_user_status');
            $table->index(['user_id', 'is_historical'], 'idx_monthly_records_user_historical');
        });

        // Indexes for payments - frequently queried columns
        Schema::table('payments', function (Blueprint $table) {
            $table->index(['user_id', 'payment_date'], 'idx_payments_user_payment_date');
            $table->index(['user_id', 'payment_method'], 'idx_payments_user_payment_method');
            $table->index(['monthly_record_id'], 'idx_payments_monthly_record_id');
        });

        // Indexes for rental_settings
        Schema::table('rental_settings', function (Blueprint $table) {
            $table->index('user_id', 'idx_rental_settings_user_id');
        });

        // Indexes for additional_expenses
        Schema::table('additional_expenses', function (Blueprint $table) {
            $table->index(['user_id', 'monthly_record_id'], 'idx_additional_expenses_user_record');
        });
    }

    public function down(): void
    {
        Schema::table('monthly_records', function (Blueprint $table) {
            $table->dropIndex('idx_monthly_records_user_year_month');
            $table->dropIndex('idx_monthly_records_user_record_date');
            $table->dropIndex('idx_monthly_records_user_status');
            $table->dropIndex('idx_monthly_records_user_historical');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_payments_user_payment_date');
            $table->dropIndex('idx_payments_user_payment_method');
            $table->dropIndex('idx_payments_monthly_record_id');
        });

        Schema::table('rental_settings', function (Blueprint $table) {
            $table->dropIndex('idx_rental_settings_user_id');
        });

        Schema::table('additional_expenses', function (Blueprint $table) {
            $table->dropIndex('idx_additional_expenses_user_record');
        });
    }
};
