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
        Schema::table('club_competition_entries', function (Blueprint $table) {
            $table->date('entry_date')->nullable()->after('club_competition_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_competition_entries', function (Blueprint $table) {
            $table->dropColumn('entry_date');
        });
    }
};
