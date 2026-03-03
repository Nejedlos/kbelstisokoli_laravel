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
        Schema::create('legacy_import_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legacy_import_batch_id')->constrained('legacy_import_batches')->cascadeOnDelete();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('detected_season_label')->nullable();
            $table->string('detected_team_slug')->nullable();
            $table->string('file_type')->default('unknown'); // players_stats, team_stats, league_table, unknown
            $table->string('content_hash', 64)->index();
            $table->string('status')->default('queued'); // queued, running, success, failed, skipped
            $table->text('error_summary')->nullable();
            $table->integer('warnings_count')->nullable();
            $table->integer('imported_rows_count')->nullable();
            $table->foreignId('import_run_id')->nullable()->constrained('external_import_runs')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_import_files');
    }
};
