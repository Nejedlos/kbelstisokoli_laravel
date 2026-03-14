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
        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('venue_id')->nullable()->after('location')->constrained('venues')->nullOnDelete();
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('primary_venue_id')->nullable()->after('category')->constrained('venues')->nullOnDelete();
        });

        Schema::table('opponents', function (Blueprint $table) {
            $table->foreignId('primary_venue_id')->nullable()->after('city')->constrained('venues')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('opponents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_venue_id');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->dropConstrainedForeignId('primary_venue_id');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('venue_id');
        });
    }
};
