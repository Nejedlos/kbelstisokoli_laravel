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
        Schema::create('external_team_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('source_key')->default('czbasketball');
            $table->foreignId('team_id')->constrained()->onDelete('cascade');
            $table->string('external_team_id');
            $table->string('base_team_url');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['source_key', 'team_id']);
            $table->unique(['source_key', 'external_team_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_team_mappings');
    }
};
