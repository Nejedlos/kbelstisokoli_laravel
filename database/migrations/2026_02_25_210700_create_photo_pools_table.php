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
        if (!Schema::hasTable('photo_pools')) {
            Schema::create('photo_pools', function (Blueprint $table) {
                $table->id();
                $table->longText('title'); // translatable JSON
                $table->string('slug')->unique();
                $table->longText('description')->nullable(); // translatable JSON
                $table->string('event_type')->nullable(); // tournament, match, training, club_event, other
                $table->date('event_date')->nullable();
                $table->boolean('is_public')->default(true);
                $table->boolean('is_visible')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_pools');
    }
};
