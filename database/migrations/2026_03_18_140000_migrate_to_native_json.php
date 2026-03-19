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
                // Vyčištění neplatných dat před konverzí na JSON
                // Prázdné řetězce nebo neplatné JSON formáty dělají MariaDB problémy při změně na JSON typ.
                foreach ($columns as $column) {
                    try {
                        // 1. Nejprve převedeme prázdné řetězce na NULL
                        DB::table($table)
                            ->where($column, '')
                            ->orWhere($column, '""')
                            ->update([$column => null]);

                        // 2. Projdeme neplatné JSON hodnoty a zkusíme je "opravit" (zabalit do uvozovek přes json_encode)
                        // Toto řeší případy jako 'aggressive' -> '"aggressive"'
                        // Používáme chunk, abychom nezahltili paměť u velkých tabulek (např. logs)
                        DB::table($table)
                            ->whereNotNull($column)
                            ->select(['id', $column])
                            ->chunkById(500, function ($rows) use ($table, $column) {
                                foreach ($rows as $row) {
                                    $value = $row->{$column};
                                    // Pokud to není validní JSON, zkusíme to zakódovat
                                    if (! $this->isValidJson($value)) {
                                        DB::table($table)
                                            ->where('id', $row->id)
                                            ->update([$column => json_encode($value, JSON_UNESCAPED_UNICODE)]);
                                    }
                                }
                            });
                    } catch (\Throwable $e) {
                        // Tichý fail - pokud např. chybí ID sloupec v tabulce, chunkById selže
                    }
                }

                Schema::table($table, function (Blueprint $tableGroup) use ($columns) {
                    foreach ($columns as $column) {
                        $tableGroup->json($column)->nullable()->change();
                    }
                });
            }
        }
    }

    /**
     * Pomocná metoda pro ověření JSONu v PHP (MariaDB JSON_VALID() nemusí být v této fázi spolehlivé)
     */
    protected function isValidJson($value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        if ($value === 'null' || $value === 'true' || $value === 'false') {
            return true;
        }

        // Zkusíme dekódovat. Pokud to není pole nebo objekt, musí to být obalené v uvozovkách
        // aby to MariaDB brala jako validní JSON string.
        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
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
                        $tableGroup->longText($column)->nullable()->change();
                    }
                });
            }
        }
    }
};
