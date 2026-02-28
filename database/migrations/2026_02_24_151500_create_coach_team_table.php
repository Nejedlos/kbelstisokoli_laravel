<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('coach_team')) {
            Schema::create('coach_team', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                // Volitelný override kontaktu pro tento tým (pokud se liší od uživatelského e-mailu)
                $table->string('email')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('coach_team');
    }
};
