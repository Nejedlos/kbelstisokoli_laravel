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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
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
    }
};
