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
        Schema::create('external_import_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('external_import_run_id')->constrained('external_import_runs')->onDelete('cascade');
            $table->string('model_type')->nullable(); // App\Models\BasketballMatch, App\Models\StatisticRow, atd.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->string('action'); // created, updated, skipped, error
            $table->longText('old_values')->nullable(); // JSON
            $table->longText('new_values')->nullable(); // JSON
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['external_import_run_id'], 'idx_import_log_run');
            $table->index(['model_type', 'model_id'], 'idx_import_log_model');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_import_logs');
    }
};
