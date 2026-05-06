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
        Schema::create('internal_analytics_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_type')->index();
            $table->string('area')->nullable()->index();
            $table->string('method', 10)->nullable();
            $table->string('path')->nullable();
            $table->string('route_name')->nullable()->index();
            $table->string('route_action')->nullable();
            $table->string('full_url_hash', 64)->nullable();
            $table->unsignedSmallInteger('status_code')->nullable()->index();
            $table->unsignedInteger('response_time_ms')->nullable()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_type')->nullable();
            $table->string('guard', 32)->nullable();
            $table->boolean('is_authenticated')->default(false)->index();
            $table->string('visitor_hash', 64)->nullable()->index();
            $table->string('session_hash', 64)->nullable()->index();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->text('user_agent')->nullable();
            $table->text('referer')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('occurred_at')->useCurrent()->index();
            $table->timestamps();

            // Kombinované indexy pro rychlejší dashboard
            $table->index(['occurred_at', 'area']);
            $table->index(['occurred_at', 'event_type']);
            $table->index(['area', 'event_type', 'occurred_at'], 'idx_area_type_occurred');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('internal_analytics_events');
    }
};
