<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // rental_settings - add user_id if not exists
        if (! Schema::hasColumn('rental_settings', 'user_id')) {
            DB::statement('ALTER TABLE rental_settings ADD COLUMN user_id BIGINT UNSIGNED NULL AFTER id');
            Schema::table('rental_settings', function (Blueprint $table) {
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // monthly_records - add user_id if not exists
        if (! Schema::hasColumn('monthly_records', 'user_id')) {
            Schema::table('monthly_records', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index('user_id');
            });
        }

        // payments - add user_id if not exists
        if (! Schema::hasColumn('payments', 'user_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index('user_id');
            });
        }

        // additional_expenses - add user_id if not exists
        if (! Schema::hasColumn('additional_expenses', 'user_id')) {
            Schema::table('additional_expenses', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('rental_settings', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('monthly_records', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('additional_expenses', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
