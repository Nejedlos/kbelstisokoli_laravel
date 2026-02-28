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
        // Indexy pro tabulku matches (Zápasy)
        Schema::table('matches', function (Blueprint $table) {
            $indexes = Schema::getIndexes('matches');
            $indexNames = array_column($indexes, 'name');

            if (! in_array('matches_team_scheduled_idx', $indexNames)) {
                $table->index(['team_id', 'scheduled_at'], 'matches_team_scheduled_idx');
            }
            if (! in_array('matches_season_status_scheduled_idx', $indexNames)) {
                $table->index(['season_id', 'status', 'scheduled_at'], 'matches_season_status_scheduled_idx');
            }
            if (! in_array('matches_scheduled_at_idx', $indexNames)) {
                $table->index('scheduled_at', 'matches_scheduled_at_idx');
            }
        });

        // Indexy pro tabulku posts (Novinky/Články)
        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $indexes = Schema::getIndexes('posts');
                $indexNames = array_column($indexes, 'name');

                if (! in_array('posts_status_publish_idx', $indexNames)) {
                    $table->index(['status', 'publish_at'], 'posts_status_publish_idx');
                }
                if (! in_array('posts_publish_at_idx', $indexNames)) {
                    $table->index('publish_at', 'posts_publish_at_idx');
                }
            });
        }

        // Indexy pro tabulku trainings (Tréninky)
        Schema::table('trainings', function (Blueprint $table) {
            $indexes = Schema::getIndexes('trainings');
            $indexNames = array_column($indexes, 'name');

            if (! in_array('trainings_starts_at_idx', $indexNames)) {
                $table->index('starts_at', 'trainings_starts_at_idx');
            }
        });

        // Indexy pro pivot tabulku team_training
        if (Schema::hasTable('team_training')) {
            Schema::table('team_training', function (Blueprint $table) {
                $indexes = Schema::getIndexes('team_training');
                $indexNames = array_column($indexes, 'name');

                if (! in_array('team_training_composite_idx', $indexNames)) {
                    $table->index(['team_id', 'training_id'], 'team_training_composite_idx');
                }
            });
        }

        // Indexy pro tabulku club_events (Akce)
        $eventsTable = Schema::hasTable('club_events') ? 'club_events' : (Schema::hasTable('events') ? 'events' : null);
        if ($eventsTable) {
            Schema::table($eventsTable, function (Blueprint $table) use ($eventsTable) {
                $indexes = Schema::getIndexes($eventsTable);
                $indexNames = array_column($indexes, 'name');

                if (! in_array('events_dates_idx', $indexNames)) {
                    $table->index(['starts_at', 'ends_at'], 'events_dates_idx');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $indexes = Schema::getIndexes('matches');
            $indexNames = array_column($indexes, 'name');

            if (in_array('matches_team_scheduled_idx', $indexNames)) {
                $table->dropIndex('matches_team_scheduled_idx');
            }
            if (in_array('matches_season_status_scheduled_idx', $indexNames)) {
                $table->dropIndex('matches_season_status_scheduled_idx');
            }
            if (in_array('matches_scheduled_at_idx', $indexNames)) {
                $table->dropIndex('matches_scheduled_at_idx');
            }
        });

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $indexes = Schema::getIndexes('posts');
                $indexNames = array_column($indexes, 'name');

                if (in_array('posts_status_publish_idx', $indexNames)) {
                    $table->dropIndex('posts_status_publish_idx');
                }
                if (in_array('posts_publish_at_idx', $indexNames)) {
                    $table->dropIndex('posts_publish_at_idx');
                }
            });
        }

        Schema::table('trainings', function (Blueprint $table) {
            $indexes = Schema::getIndexes('trainings');
            $indexNames = array_column($indexes, 'name');

            if (in_array('trainings_starts_at_idx', $indexNames)) {
                $table->dropIndex('trainings_starts_at_idx');
            }
        });

        if (Schema::hasTable('team_training')) {
            Schema::table('team_training', function (Blueprint $table) {
                $indexes = Schema::getIndexes('team_training');
                $indexNames = array_column($indexes, 'name');

                if (in_array('team_training_composite_idx', $indexNames)) {
                    $table->dropIndex('team_training_composite_idx');
                }
            });
        }

        $eventsTable = Schema::hasTable('club_events') ? 'club_events' : (Schema::hasTable('events') ? 'events' : null);
        if ($eventsTable) {
            Schema::table($eventsTable, function (Blueprint $table) use ($eventsTable) {
                $indexes = Schema::getIndexes($eventsTable);
                $indexNames = array_column($indexes, 'name');

                if (in_array('events_dates_idx', $indexNames)) {
                    $table->dropIndex('events_dates_idx');
                }
            });
        }
    }
};
