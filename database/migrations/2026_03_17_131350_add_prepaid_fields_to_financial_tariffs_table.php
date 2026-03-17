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
        // 1. prepaid_events_count
        try {
            Schema::table('financial_tariffs', function (Blueprint $table) {
                $table->integer('prepaid_events_count')->nullable()->after('type');
            });
        } catch (\Throwable $e) {
            if (!str_contains($e->getMessage(), '1060')) throw $e;
        }

        // 2. extra_event_amount
        try {
            Schema::table('financial_tariffs', function (Blueprint $table) {
                $table->decimal('extra_event_amount', 10, 2)->nullable()->after('prepaid_events_count');
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
                $table->dropColumn(['prepaid_events_count', 'extra_event_amount']);
            } catch (\Throwable $e) {
                if (!str_contains($e->getMessage(), '1091')) throw $e;
            }
        });
    }
};
