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
        Schema::create('internal_analytics_daily_summaries', function (Blueprint $table) {
            $table->id();
            $table->date('date')->index();
            $table->string('area')->nullable()->index();
            $table->string('event_type')->index();
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->unsignedInteger('unique_users')->default(0);
            $table->unsignedInteger('avg_response_time_ms')->nullable();
            $table->unsignedInteger('max_response_time_ms')->nullable();
            $table->unsignedInteger('status_2xx_count')->default(0);
            $table->unsignedInteger('status_3xx_count')->default(0);
            $table->unsignedInteger('status_4xx_count')->default(0);
            $table->unsignedInteger('status_5xx_count')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['date', 'area', 'event_type'], 'idx_summary_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_analytics_daily_summaries');
    }
};
