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
        if (! Schema::hasTable('performance_test_results')) {
            Schema::create('performance_test_results', function (Blueprint $table) {
                $table->id();
                $table->string('scenario'); // standard, aggressive, ultra
                $table->string('url');
                $table->string('label'); // Homepage, Match Detail, atd.
                $table->string('section'); // public, member, admin
                $table->float('duration_ms');
                $table->integer('query_count');
                $table->float('query_time_ms');
                $table->float('memory_mb');
                $table->boolean('opcache_enabled')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('performance_test_results');
    }
};
