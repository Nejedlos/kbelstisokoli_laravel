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
        Schema::create('opponent_merge_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_opponent_id')->constrained('opponents')->onDelete('cascade');
            $table->foreignId('target_opponent_id')->constrained('opponents')->onDelete('cascade');
            $table->integer('similarity')->nullable();
            $table->string('status')->default('pending'); // pending, rejected, accepted
            $table->timestamps();

            $table->unique(['source_opponent_id', 'target_opponent_id'], 'om_unique_pair');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('opponent_merge_suggestions');
    }
};
