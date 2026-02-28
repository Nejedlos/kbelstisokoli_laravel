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
        Schema::table('photo_pools', function (Blueprint $table) {
            $table->json('pending_import_queue')->nullable()->after('is_visible');
            $table->boolean('is_processing_import')->default(false)->after('pending_import_queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('photo_pools', function (Blueprint $table) {
            $table->dropColumn(['pending_import_queue', 'is_processing_import']);
        });
    }
};
