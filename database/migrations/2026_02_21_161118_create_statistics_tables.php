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
        // 1. Statistic Sets (Sady statistik)
        Schema::create('statistic_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('type'); // league_table, player_stats, team_summary, custom_competition
            $table->text('description')->nullable();
            $table->boolean('is_visible')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('source_type')->default('manual'); // manual, external_import, hybrid
            $table->json('scope')->nullable(); // season_id, team_id, match_id, etc.
            $table->json('column_config')->nullable(); // Definice sloupců (key, label, type, sortable, etc.)
            $table->json('settings')->nullable(); // Rendering/formatting settings
            $table->string('status')->default('draft'); // draft, published, archived
            $table->timestamps();
        });

        // 2. Statistic Rows (Jednotlivé řádky s daty)
        Schema::create('statistic_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statistic_set_id')->constrained()->onDelete('cascade');
            $table->foreignId('player_id')->nullable()->constrained('users')->onDelete('set null'); // Předpokládám, že hráči jsou v users
            $table->foreignId('team_id')->nullable()->constrained('teams')->onDelete('set null');
            $table->foreignId('basketball_match_id')->nullable()->constrained('matches')->onDelete('set null');
            $table->foreignId('season_id')->nullable()->constrained('seasons')->onDelete('set null');
            $table->string('row_label')->nullable(); // Fallback label (např. název týmu, který není v DB)
            $table->unsignedInteger('row_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->json('values'); // Samotná data statistik (JSON payload)
            $table->json('source_metadata')->nullable(); // Provenance: manual/imported/ai-normalized
            $table->timestamps();
        });

        // 3. External Statistic Sources (Konfigurace externích importů)
        Schema::create('external_stat_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('source_url');
            $table->string('source_type')->default('html_table'); // html_table, page_extract, api
            $table->json('extractor_config')->nullable(); // Pravidla pro extrakci (např. CSS selector, table index)
            $table->json('mapping_config')->nullable(); // Mapování na naše StatisticSet/Rows
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_status')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_stat_sources');
        Schema::dropIfExists('statistic_rows');
        Schema::dropIfExists('statistic_sets');
    }
};
