<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Před spuštěním této migrace je NUTNÉ mít v .env nastaveno:
        // DB_VERSION=8.0
        // DB_MARIADB=false

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $tables = [
            'announcements' => ['title', 'message', 'cta_label'],
            'matches' => ['notes_public', 'metadata'],
            'club_competitions' => ['name', 'description', 'metric_description', 'rules'],
            'club_events' => ['title', 'description', 'metadata'],
            'fine_templates' => ['name', 'description', 'metadata'],
            'galleries' => ['title', 'description'],
            'menu_items' => ['label'],
            'pages' => ['title', 'content'],
            'partners' => ['label', 'description'],
            'permissions' => ['display_name'],
            'photo_pools' => ['title', 'description', 'pending_import_queue'],
            'posts' => ['title', 'excerpt', 'content'],
            'post_categories' => ['name', 'description'],
            'roles' => ['display_name'],
            'seo_metadatas' => ['title', 'description', 'og_title', 'og_description', 'keywords', 'structured_data_override'],
            'settings' => ['value'],
            'statistic_sets' => ['name', 'description', 'scope', 'column_config', 'settings'],
            'teams' => ['name', 'description'],
            'users' => ['notification_preferences', 'metadata'],
            'feedback_reports' => ['viewport', 'screen', 'meta'],
            'ai_documents' => ['keywords', 'metadata'],
            'ai_request_logs' => ['token_usage', 'metadata'],
            'audit_logs' => ['metadata', 'changes'],
            'external_import_logs' => ['old_values', 'new_values'],
            'external_stat_sources' => ['extractor_config', 'mapping_config'],
            'financial_tariffs' => ['installment_plan', 'metadata'],
            'match_predictions' => ['factors', 'explanation_points'],
            'page_blocks' => ['data', 'custom_attributes'],
            'statistic_rows' => ['values', 'source_metadata'],
            'ai_settings' => ['model_presets'],
            'attendances' => ['metadata'],
            'club_competition_entries' => ['metadata'],
            'competition_standings' => ['metadata'],
            'external_entity_mappings' => ['metadata'],
            'external_import_runs' => ['metadata'],
            'external_player_matches' => ['metadata'],
            'external_team_mappings' => ['metadata'],
            'external_team_season_configs' => ['metadata'],
            'finance_charges' => ['metadata'],
            'finance_payments' => ['metadata'],
            'leads' => ['payload'],
            'legacy_import_batches' => ['metadata'],
            'opponents' => ['metadata'],
            'player_profiles' => ['metadata'],
            'trainings' => ['metadata'],
            'user_season_configs' => ['metadata'],
            'venues' => ['metadata'],
        ];

        foreach ($tables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableGroup) use ($columns) {
                    foreach ($columns as $column) {
                        $tableGroup->json($column)->nullable()->change();
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // V down metodě bychom se vraceli na LONGTEXT, ale pro MySQL 8
        // je JSON nativní a bezpečnější. Ponecháme JSON nebo se vrátíme k LONGTEXT.
        // Vzhledem k dřívější kompatibilitě se vrátíme k LONGTEXT.

        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $tables = [
            'announcements' => ['title', 'message', 'cta_label'],
            'matches' => ['notes_public', 'metadata'],
            'club_competitions' => ['name', 'description', 'metric_description', 'rules'],
            'club_events' => ['title', 'description', 'metadata'],
            'fine_templates' => ['name', 'description', 'metadata'],
            'galleries' => ['title', 'description'],
            'menu_items' => ['label'],
            'pages' => ['title', 'content'],
            'partners' => ['label', 'description'],
            'permissions' => ['display_name'],
            'photo_pools' => ['title', 'description', 'pending_import_queue'],
            'posts' => ['title', 'excerpt', 'content'],
            'post_categories' => ['name', 'description'],
            'roles' => ['display_name'],
            'seo_metadatas' => ['title', 'description', 'og_title', 'og_description', 'keywords', 'structured_data_override'],
            'settings' => ['value'],
            'statistic_sets' => ['name', 'description', 'scope', 'column_config', 'settings'],
            'teams' => ['name', 'description'],
            'users' => ['notification_preferences', 'metadata'],
            'feedback_reports' => ['viewport', 'screen', 'meta'],
            'ai_documents' => ['keywords', 'metadata'],
            'ai_request_logs' => ['token_usage', 'metadata'],
            'audit_logs' => ['metadata', 'changes'],
            'external_import_logs' => ['old_values', 'new_values'],
            'external_stat_sources' => ['extractor_config', 'mapping_config'],
            'financial_tariffs' => ['installment_plan', 'metadata'],
            'match_predictions' => ['factors', 'explanation_points'],
            'page_blocks' => ['data', 'custom_attributes'],
            'statistic_rows' => ['values', 'source_metadata'],
            'ai_settings' => ['model_presets'],
            'attendances' => ['metadata'],
            'club_competition_entries' => ['metadata'],
            'competition_standings' => ['metadata'],
            'external_entity_mappings' => ['metadata'],
            'external_import_runs' => ['metadata'],
            'external_player_matches' => ['metadata'],
            'external_team_mappings' => ['metadata'],
            'external_team_season_configs' => ['metadata'],
            'finance_charges' => ['metadata'],
            'finance_payments' => ['metadata'],
            'leads' => ['payload'],
            'legacy_import_batches' => ['metadata'],
            'opponents' => ['metadata'],
            'player_profiles' => ['metadata'],
            'trainings' => ['metadata'],
            'user_season_configs' => ['metadata'],
            'venues' => ['metadata'],
        ];

        foreach ($tables as $table => $columns) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $tableGroup) use ($columns) {
                    foreach ($columns as $column) {
                        $tableGroup->json($column)->nullable()->change();
                    }
                });
            }
        }
    }
};
