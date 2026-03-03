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
        Schema::create('external_import_runs', function (Blueprint $table) {
            $table->id();
            $table->string('source_key'); // "czbasketball"
            $table->foreignId('season_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('team_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('run_type'); // team_page, matches_list, match_detail, player_detail
            $table->string('target_external_id')->nullable();
            $table->string('status'); // queued, running, success, partial_failed, failed, skipped
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->integer('extracted_count')->nullable();
            $table->integer('imported_count')->nullable();
            $table->integer('skipped_count')->nullable();
            $table->string('content_hash', 64)->nullable(); // sha256
            $table->text('error_summary')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['source_key', 'season_id', 'run_type', 'target_external_id'], 'idx_import_run_target');
            $table->index(['status', 'started_at'], 'idx_import_run_status_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_import_runs');
    }
};
