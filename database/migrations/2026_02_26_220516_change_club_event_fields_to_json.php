<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('club_events', function (Blueprint $table) {
                $table->longText('title')->change();
                $table->longText('description')->nullable()->change();
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        $table = $prefix.'club_events';

        try {
            // Používáme LONGTEXT místo JSON pro Webglobe kompatibilitu.
            // Laravel cast v modelu se postará o zbytek.
            DB::statement("ALTER TABLE {$table} MODIFY title LONGTEXT NOT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY description LONGTEXT NULL");
        } catch (Throwable $e) {
            // Ignore
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('club_events', function (Blueprint $table) {
                $table->longText('title')->change();
                $table->longText('description')->nullable()->change();
            });

            return;
        }

        $prefix = DB::getTablePrefix();
        $table = $prefix.'club_events';

        try {
            DB::statement("ALTER TABLE {$table} MODIFY title LONGTEXT NOT NULL");
            DB::statement("ALTER TABLE {$table} MODIFY description LONGTEXT NULL");
        } catch (Throwable $e) {
            // Ignore
        }
    }
};
