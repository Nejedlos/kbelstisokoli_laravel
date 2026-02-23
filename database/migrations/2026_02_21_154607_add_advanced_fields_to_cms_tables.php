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
            $table->text('head_code')->nullable()->after('status');
            $table->text('footer_code')->nullable()->after('head_code');
        });

        Schema::table('posts', function (Blueprint $table) {
            $table->text('head_code')->nullable()->after('status');
            $table->text('footer_code')->nullable()->after('head_code');
        });

        Schema::table('page_blocks', function (Blueprint $table) {
            $table->string('custom_id')->nullable();
            $table->string('custom_class')->nullable();
            $table->longText('custom_attributes')->nullable();
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
