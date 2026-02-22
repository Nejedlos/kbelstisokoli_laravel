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
        Schema::table('player_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('player_profiles', 'preferred_jersey_number')) {
                $table->string('preferred_jersey_number')->nullable()->after('jersey_number');
            }
            if (!Schema::hasColumn('player_profiles', 'dominant_hand')) {
                $table->string('dominant_hand')->nullable()->after('preferred_jersey_number');
            }
            if (!Schema::hasColumn('player_profiles', 'height_cm')) {
                $table->integer('height_cm')->nullable()->after('dominant_hand');
            }
            if (!Schema::hasColumn('player_profiles', 'weight_kg')) {
                $table->integer('weight_kg')->nullable()->after('height_cm');
            }
            if (!Schema::hasColumn('player_profiles', 'jersey_size')) {
                $table->string('jersey_size')->nullable()->after('weight_kg');
            }
            if (!Schema::hasColumn('player_profiles', 'shorts_size')) {
                $table->string('shorts_size')->nullable()->after('jersey_size');
            }
            if (!Schema::hasColumn('player_profiles', 'license_number')) {
                $table->string('license_number')->nullable()->after('shorts_size');
            }
            if (!Schema::hasColumn('player_profiles', 'medical_note')) {
                $table->text('medical_note')->nullable()->after('license_number');
            }
            if (!Schema::hasColumn('player_profiles', 'coach_note')) {
                $table->text('coach_note')->nullable()->after('medical_note');
            }
            if (!Schema::hasColumn('player_profiles', 'joined_team_at')) {
                $table->date('joined_team_at')->nullable()->after('coach_note');
            }
            if (!Schema::hasColumn('player_profiles', 'primary_team_id')) {
                $table->foreignId('primary_team_id')->nullable()->constrained('teams')->nullOnDelete()->after('joined_team_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('player_profiles', function (Blueprint $table) {
            $table->dropForeign(['primary_team_id']);
            $table->dropColumn([
                'preferred_jersey_number', 'dominant_hand', 'height_cm', 'weight_kg',
                'jersey_size', 'shorts_size', 'license_number', 'medical_note',
                'coach_note', 'joined_team_at', 'primary_team_id'
            ]);
        });
    }
};
