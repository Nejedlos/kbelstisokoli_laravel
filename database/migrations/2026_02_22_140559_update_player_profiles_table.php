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
            $table->string('preferred_jersey_number')->nullable()->after('jersey_number');
            $table->string('dominant_hand')->nullable()->after('preferred_jersey_number');
            $table->integer('height_cm')->nullable()->after('dominant_hand');
            $table->integer('weight_kg')->nullable()->after('height_cm');
            $table->string('jersey_size')->nullable()->after('weight_kg');
            $table->string('shorts_size')->nullable()->after('jersey_size');
            $table->string('license_number')->nullable()->after('shorts_size');
            $table->text('medical_note')->nullable()->after('license_number');
            $table->text('coach_note')->nullable()->after('medical_note');
            $table->date('joined_team_at')->nullable()->after('coach_note');
            $table->foreignId('primary_team_id')->nullable()->constrained('teams')->nullOnDelete()->after('joined_team_at');
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
