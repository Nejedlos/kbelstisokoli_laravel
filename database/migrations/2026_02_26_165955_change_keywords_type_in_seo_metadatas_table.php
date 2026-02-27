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
        if (Schema::hasTable('seo_metadatas')) {
            if (config('database.default') === 'sqlite') {
                Schema::table('seo_metadatas', function (Blueprint $table) {
                    $table->text('keywords')->nullable()->change();
                });
            } else {
                // MySQL/MariaDB syntaxe pro změnu typu sloupce bez metody change()
                // která je na některých hostinzích (Webglobe) problematická přes Doctrine DBAL.
                $prefix = DB::getTablePrefix();
                DB::statement("ALTER TABLE {$prefix}seo_metadatas MODIFY keywords LONGTEXT NULL");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('seo_metadatas')) {
            if (config('database.default') === 'sqlite') {
                Schema::table('seo_metadatas', function (Blueprint $table) {
                    $table->string('keywords', 255)->nullable()->change();
                });
            } else {
                $prefix = DB::getTablePrefix();
                DB::statement("ALTER TABLE {$prefix}seo_metadatas MODIFY keywords VARCHAR(255) NULL");
            }
        }
    }
};
