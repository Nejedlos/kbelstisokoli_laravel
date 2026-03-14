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
        Schema::table('external_import_runs', function (Blueprint $table) {
            $table->integer('total_count')->nullable()->after('skipped_count');
            $table->decimal('progress_percent', 5, 2)->default(0)->after('total_count');
            $table->string('current_item_label', 255)->nullable()->after('progress_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('external_import_runs', function (Blueprint $table) {
            $table->dropColumn(['total_count', 'progress_percent', 'current_item_label']);
        });
    }
};
