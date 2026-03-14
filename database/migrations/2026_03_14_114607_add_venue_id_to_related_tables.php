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
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('matches', function (Blueprint $table) {
                $table->foreignId('venue_id')->nullable()->after('location')->constrained('venues')->nullOnDelete();
            });

            Schema::table('teams', function (Blueprint $table) {
                $table->foreignId('primary_venue_id')->nullable()->after('category')->constrained('venues')->nullOnDelete();
            });

            Schema::table('opponents', function (Blueprint $table) {
                $table->foreignId('primary_venue_id')->nullable()->after('city')->constrained('venues')->nullOnDelete();
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $venuesTable = $prefix.'venues';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}matches ADD COLUMN venue_id BIGINT UNSIGNED NULL AFTER location");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}matches ADD CONSTRAINT fk_matches_venue_id FOREIGN KEY (venue_id) REFERENCES {$venuesTable}(id) ON DELETE SET NULL");
        } catch (\Throwable $e) {}

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}teams ADD COLUMN primary_venue_id BIGINT UNSIGNED NULL AFTER category");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}teams ADD CONSTRAINT fk_teams_venue_id FOREIGN KEY (primary_venue_id) REFERENCES {$venuesTable}(id) ON DELETE SET NULL");
        } catch (\Throwable $e) {}

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}opponents ADD COLUMN primary_venue_id BIGINT UNSIGNED NULL AFTER city");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}opponents ADD CONSTRAINT fk_opponents_venue_id FOREIGN KEY (primary_venue_id) REFERENCES {$venuesTable}(id) ON DELETE SET NULL");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('opponents', function (Blueprint $table) {
                $table->dropConstrainedForeignId('primary_venue_id');
            });

            Schema::table('teams', function (Blueprint $table) {
                $table->dropConstrainedForeignId('primary_venue_id');
            });

            Schema::table('matches', function (Blueprint $table) {
                $table->dropConstrainedForeignId('venue_id');
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}opponents DROP FOREIGN KEY fk_opponents_venue_id");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}opponents DROP COLUMN primary_venue_id");
        } catch (\Throwable $e) {}

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}teams DROP FOREIGN KEY fk_teams_venue_id");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}teams DROP COLUMN primary_venue_id");
        } catch (\Throwable $e) {}

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}matches DROP FOREIGN KEY fk_matches_venue_id");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$prefix}matches DROP COLUMN venue_id");
        } catch (\Throwable $e) {}
    }
};
