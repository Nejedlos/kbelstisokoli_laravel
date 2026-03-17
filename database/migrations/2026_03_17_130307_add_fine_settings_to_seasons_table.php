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
        Schema::table('seasons', function (Blueprint $table) {
            $table->decimal('fine_no_response', 12, 2)->default(0)->after('is_active');
            $table->decimal('fine_no_show', 12, 2)->default(0)->after('fine_no_response');
            $table->decimal('fine_unannounced_show', 12, 2)->default(0)->after('fine_no_show');
            $table->decimal('fine_excused_show', 12, 2)->default(0)->after('fine_unannounced_show');
            $table->decimal('fine_missed_free_throw', 12, 2)->default(0)->after('fine_excused_show');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            $table->dropColumn([
                'fine_no_response',
                'fine_no_show',
                'fine_unannounced_show',
                'fine_excused_show',
                'fine_missed_free_throw',
            ]);
        });
    }
};
