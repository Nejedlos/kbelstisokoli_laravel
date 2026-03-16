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
        Schema::create('competition_standings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('season_id')->constrained()->cascadeOnDelete();
            $table->string('competition_url')->index();
            $table->string('competition_name')->nullable();
            $table->string('team_name');
            $table->integer('rank')->default(0);
            $table->integer('gp')->default(0);
            $table->integer('w')->default(0);
            $table->integer('l')->default(0);
            $table->string('score')->nullable();
            $table->integer('points')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamps();

            $table->unique(['season_id', 'competition_url', 'team_name'], 'unique_standing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('competition_standings');
    }
};
