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
        if (!Schema::hasTable('user_season_configs')) {
            Schema::create('user_season_configs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('season_id')->constrained()->onDelete('cascade');
                $table->foreignId('financial_tariff_id')->constrained()->onDelete('cascade');
                $table->integer('billing_start_month')->nullable();
                $table->integer('billing_end_month')->nullable();
                $table->integer('exemption_start_month')->nullable();
                $table->integer('exemption_end_month')->nullable();
                $table->boolean('track_attendance')->default(true);
                $table->decimal('opening_balance', 12, 2)->default(0);
                $table->longText('metadata')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'season_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_season_configs');
    }
};
