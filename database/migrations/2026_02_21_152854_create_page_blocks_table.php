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
        if (! Schema::hasTable('page_blocks')) {
            Schema::create('page_blocks', function (Blueprint $table) {
                $table->id();
                $table->foreignId('page_id')->constrained()->onDelete('cascade');
                $table->string('block_type');
                $table->unsignedInteger('sort_order')->default(0);
                $table->boolean('is_visible')->default(true);
                $table->longText('data')->nullable();
                $table->string('variant')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_blocks');
    }
};
