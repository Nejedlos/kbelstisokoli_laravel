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
            Schema::table('external_player_matches', function (Blueprint $table) {
                $table->string('number', 10)->nullable()->after('opponent_name');
                $table->boolean('is_starter')->default(false)->after('number');
                $table->boolean('is_captain')->default(false)->after('is_starter');

                $table->integer('plus_minus')->nullable()->after('valuation');
                $table->integer('rebounds_offensive')->nullable()->after('plus_minus');
                $table->integer('rebounds_defensive')->nullable()->after('rebounds_offensive');
                $table->integer('rebounds_total')->nullable()->after('rebounds_defensive');
                $table->integer('assists')->nullable()->after('rebounds_total');
                $table->integer('steals')->nullable()->after('assists');
                $table->integer('turnovers')->nullable()->after('steals');
                $table->integer('blocks')->nullable()->after('turnovers');
                $table->integer('fouls_drawn')->nullable()->after('blocks');
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'external_player_matches';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN number VARCHAR(10) NULL AFTER opponent_name");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN is_starter TINYINT(1) NOT NULL DEFAULT 0 AFTER number");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN is_captain TINYINT(1) NOT NULL DEFAULT 0 AFTER is_starter");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN plus_minus INT NULL AFTER valuation");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN rebounds_offensive INT NULL AFTER plus_minus");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN rebounds_defensive INT NULL AFTER rebounds_offensive");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN rebounds_total INT NULL AFTER rebounds_defensive");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN assists INT NULL AFTER rebounds_total");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN steals INT NULL AFTER assists");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN turnovers INT NULL AFTER steals");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN blocks INT NULL AFTER turnovers");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} ADD COLUMN fouls_drawn INT NULL AFTER blocks");
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() === 'sqlite') {
            Schema::table('external_player_matches', function (Blueprint $table) {
                $table->dropColumn([
                    'number',
                    'is_starter',
                    'is_captain',
                    'plus_minus',
                    'rebounds_offensive',
                    'rebounds_defensive',
                    'rebounds_total',
                    'assists',
                    'steals',
                    'turnovers',
                    'blocks',
                    'fouls_drawn',
                ]);
            });

            return;
        }

        $prefix = \Illuminate\Support\Facades\DB::getTablePrefix();
        $table = $prefix.'external_player_matches';

        try {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS number");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS is_starter");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS is_captain");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS plus_minus");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS rebounds_offensive");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS rebounds_defensive");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS rebounds_total");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS assists");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS steals");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS turnovers");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS blocks");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE {$table} DROP COLUMN IF EXISTS fouls_drawn");
        } catch (\Throwable $e) {}
    }
};
