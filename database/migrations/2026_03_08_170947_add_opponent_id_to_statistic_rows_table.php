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
        Schema::table('statistic_rows', function (Blueprint $table) {
            $table->foreignId('opponent_id')->nullable()->after('team_id')->constrained('opponents')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('statistic_rows', function (Blueprint $table) {
            $table->dropForeign(['opponent_id']);
            $table->dropColumn('opponent_id');
        });
    }
};
