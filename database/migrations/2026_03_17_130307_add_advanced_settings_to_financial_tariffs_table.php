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
        Schema::table('financial_tariffs', function (Blueprint $table) {
            $table->string('type')->default('flat')->after('unit');
            $table->json('installment_plan')->nullable()->after('type');
            $table->boolean('calculate_attendance_fines')->default(false)->after('installment_plan');
            $table->boolean('calculate_th_fines')->default(false)->after('calculate_attendance_fines');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_tariffs', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'installment_plan',
                'calculate_attendance_fines',
                'calculate_th_fines',
            ]);
        });
    }
};
