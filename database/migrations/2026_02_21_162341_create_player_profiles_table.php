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
        if (! Schema::hasTable('player_profiles')) {
            Schema::create('player_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
                $table->string('jersey_number')->nullable();
                $table->string('position')->nullable(); // např. PG, SG, SF, PF, C
                $table->text('public_bio')->nullable();
                $table->text('private_note')->nullable(); // pouze pro trenéry/adminy
                $table->boolean('is_active')->default(true);
                $table->longText('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('player_profile_team')) {
            Schema::create('player_profile_team', function (Blueprint $table) {
                $table->id();
                $table->foreignId('player_profile_id')->constrained()->onDelete('cascade');
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->string('role_in_team')->default('player'); // player, captain, assistant
                $table->boolean('is_primary_team')->default(false);
                $table->date('active_from')->nullable();
                $table->date('active_to')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('player_profile_team');
        Schema::dropIfExists('player_profiles');
    }
};
