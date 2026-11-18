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
        // Drop existing unique constraint on year + month (without user_id) if it exists
        if (Schema::hasIndex('monthly_records', 'monthly_records_year_month_unique')) {
            Schema::table('monthly_records', function (Blueprint $table) {
                $table->dropUnique('monthly_records_year_month_unique');
            });
        }

        // Add unique constraint including user_id for multi-tenancy support
        if (! Schema::hasIndex('monthly_records', 'monthly_records_year_month_user_id_unique')) {
            Schema::table('monthly_records', function (Blueprint $table) {
                $table->unique(['year', 'month', 'user_id'], 'monthly_records_year_month_user_id_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the multi-tenant unique constraint
        Schema::table('monthly_records', function (Blueprint $table) {
            $table->dropUnique('monthly_records_year_month_user_id_unique');
        });

        // Restore the original unique constraint on year + month only
        Schema::table('monthly_records', function (Blueprint $table) {
            $table->unique(['year', 'month'], 'monthly_records_year_month_unique');
        });
    }
};
