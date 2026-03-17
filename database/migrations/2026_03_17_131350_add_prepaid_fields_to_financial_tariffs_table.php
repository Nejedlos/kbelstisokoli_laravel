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
            $table->integer('prepaid_events_count')->nullable()->after('type');
            $table->decimal('extra_event_amount', 10, 2)->nullable()->after('prepaid_events_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_tariffs', function (Blueprint $table) {
            $table->dropColumn(['prepaid_events_count', 'extra_event_amount']);
        });
    }
};
