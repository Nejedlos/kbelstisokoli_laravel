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
        $columns = [
            ['fine_no_response', 12, 2, 'is_active'],
            ['fine_no_show', 12, 2, 'fine_no_response'],
            ['fine_unannounced_show', 12, 2, 'fine_no_show'],
            ['fine_excused_show', 12, 2, 'fine_unannounced_show'],
            ['fine_missed_free_throw', 12, 2, 'fine_excused_show'],
        ];

        foreach ($columns as $column) {
            try {
                Schema::table('seasons', function (Blueprint $table) use ($column) {
                    $table->decimal($column[0], $column[1], $column[2])->default(0)->after($column[3]);
                });
            } catch (\Throwable $e) {
                if (!str_contains($e->getMessage(), '1060')) throw $e;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seasons', function (Blueprint $table) {
            try {
                $table->dropColumn([
                    'fine_no_response',
                    'fine_no_show',
                    'fine_unannounced_show',
                    'fine_excused_show',
                    'fine_missed_free_throw',
                ]);
            } catch (\Throwable $e) {
                if (!str_contains($e->getMessage(), '1091')) throw $e;
            }
        });
    }
};
