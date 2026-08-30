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
        $this->addIndexIfMissing('matches', ['scheduled_at'], 'matches_scheduled_at_index');
        $this->addIndexIfMissing('matches', ['status'], 'matches_status_index');

        $this->addIndexIfMissing('trainings', ['starts_at'], 'trainings_starts_at_index');
        $this->addIndexIfMissing('trainings', ['ends_at'], 'trainings_ends_at_index');

        if (Schema::hasTable('events')) {
            $this->addIndexIfMissing('events', ['starts_at'], 'events_starts_at_index');
            $this->addIndexIfMissing('events', ['ends_at'], 'events_ends_at_index');
        }

        if (Schema::hasTable('club_events')) {
            $this->addIndexIfMissing('club_events', ['starts_at'], 'club_events_starts_at_index');
            $this->addIndexIfMissing('club_events', ['ends_at'], 'club_events_ends_at_index');
        }

        $this->addIndexIfMissing('seasons', ['is_active'], 'seasons_is_active_index');
        $this->addIndexIfMissing('teams', ['category'], 'teams_category_index');
    }

    /**
     * Add an index only when no existing index already covers the same columns.
     * This keeps fresh SQLite tests and repeatedly deployed production migrations safe.
     */
    private function addIndexIfMissing(string $tableName, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $normalizedColumns = array_values($columns);
        $alreadyIndexed = collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index) => array_values($index['columns'] ?? []) === $normalizedColumns);

        if ($alreadyIndexed) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName): void {
            $table->index($columns, $indexName);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropIndexIfExists('teams', 'teams_category_index');
        $this->dropIndexIfExists('seasons', 'seasons_is_active_index');
        $this->dropIndexIfExists('club_events', 'club_events_starts_at_index');
        $this->dropIndexIfExists('club_events', 'club_events_ends_at_index');
        $this->dropIndexIfExists('events', 'events_starts_at_index');
        $this->dropIndexIfExists('events', 'events_ends_at_index');
        $this->dropIndexIfExists('trainings', 'trainings_starts_at_index');
        $this->dropIndexIfExists('trainings', 'trainings_ends_at_index');
        $this->dropIndexIfExists('matches', 'matches_scheduled_at_index');
        $this->dropIndexIfExists('matches', 'matches_status_index');
    }

    private function dropIndexIfExists(string $tableName, string $indexName): void
    {
        if (! Schema::hasTable($tableName)) {
            return;
        }

        $exists = collect(Schema::getIndexes($tableName))
            ->contains(fn (array $index) => ($index['name'] ?? null) === $indexName);

        if (! $exists) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($indexName): void {
            $table->dropIndex($indexName);
        });
    }
};
