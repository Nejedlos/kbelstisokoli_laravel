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
        Schema::table('feedback_reports', function (Blueprint $table) {
            $table->string('performance_path')->nullable()->after('clicks_path');
            $table->string('dom_path')->nullable()->after('performance_path');
            $table->string('breadcrumbs_path')->nullable()->after('dom_path');
            $table->string('correlation_id')->nullable()->after('breadcrumbs_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback_reports', function (Blueprint $table) {
            $table->dropColumn(['performance_path', 'dom_path', 'breadcrumbs_path', 'correlation_id']);
        });
    }
};
