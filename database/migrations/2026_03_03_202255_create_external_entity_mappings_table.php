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
        Schema::create('external_entity_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('source_key')->default('czbasketball');
            $table->foreignId('season_id')->constrained()->onDelete('cascade');
            $table->string('entity_type'); // player, match, opponent
            $table->string('external_id');
            $table->string('internal_type')->nullable(); // User, BasketballMatch, Opponent
            $table->unsignedBigInteger('internal_id')->nullable();
            $table->string('identity_key');
            $table->float('confidence')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->unique(['source_key', 'season_id', 'entity_type', 'external_id'], 'ext_entity_unique');
            $table->index(['source_key', 'season_id', 'entity_type', 'identity_key'], 'ext_entity_identity_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_entity_mappings');
    }
};
