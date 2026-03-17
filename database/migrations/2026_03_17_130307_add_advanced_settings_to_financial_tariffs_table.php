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
        // 1. type
        try {
            Schema::table('financial_tariffs', function (Blueprint $table) {
                $table->string('type')->default('flat')->after('unit');
            });
        } catch (\Throwable $e) {
            if (!str_contains($e->getMessage(), '1060')) throw $e;
        }

        // 2. installment_plan (používáme longText místo json pro Webglobe)
        try {
            Schema::table('financial_tariffs', function (Blueprint $table) {
                $table->longText('installment_plan')->nullable()->after('type');
            });
        } catch (\Throwable $e) {
            if (!str_contains($e->getMessage(), '1060')) throw $e;
        }

        // 3. calculate_attendance_fines
        try {
            Schema::table('financial_tariffs', function (Blueprint $table) {
                $table->boolean('calculate_attendance_fines')->default(false)->after('installment_plan');
            });
        } catch (\Throwable $e) {
            if (!str_contains($e->getMessage(), '1060')) throw $e;
        }

        // 4. calculate_th_fines
        try {
            Schema::table('financial_tariffs', function (Blueprint $table) {
                $table->boolean('calculate_th_fines')->default(false)->after('calculate_attendance_fines');
            });
        } catch (\Throwable $e) {
            if (!str_contains($e->getMessage(), '1060')) throw $e;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_tariffs', function (Blueprint $table) {
            try {
                $table->dropColumn([
                    'type',
                    'installment_plan',
                    'calculate_attendance_fines',
                    'calculate_th_fines',
                ]);
            } catch (\Throwable $e) {
                // Ignore "Column not found" (1091)
                if (!str_contains($e->getMessage(), '1091')) throw $e;
            }
        });
    }
};
