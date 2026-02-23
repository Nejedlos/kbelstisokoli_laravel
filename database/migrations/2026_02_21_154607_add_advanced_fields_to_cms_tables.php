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
        Schema::table('pages', function (Blueprint $table) {
            if (!Schema::hasColumn('pages', 'head_code')) {
                $table->text('head_code')->nullable()->after('status');
            }
            if (!Schema::hasColumn('pages', 'footer_code')) {
                $table->text('footer_code')->nullable()->after('head_code');
            }
        });

        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'head_code')) {
                $table->text('head_code')->nullable()->after('status');
            }
            if (!Schema::hasColumn('posts', 'footer_code')) {
                $table->text('footer_code')->nullable()->after('head_code');
            }
        });

        Schema::table('page_blocks', function (Blueprint $table) {
            if (!Schema::hasColumn('page_blocks', 'custom_id')) {
                $table->string('custom_id')->nullable();
            }
            if (!Schema::hasColumn('page_blocks', 'custom_class')) {
                $table->string('custom_class')->nullable();
            }
            if (!Schema::hasColumn('page_blocks', 'custom_attributes')) {
                $table->json('custom_attributes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['head_code', 'footer_code']);
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['head_code', 'footer_code']);
        });

        Schema::table('page_blocks', function (Blueprint $table) {
            $table->dropColumn(['custom_id', 'custom_class', 'custom_attributes']);
        });
    }
};
