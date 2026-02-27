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
        if (!Schema::hasTable('attendances')) {
            Schema::create('attendances', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->morphs('attendable'); // trénink, zápas, klubová akce
                $table->string('planned_status')->default('pending'); // pending, confirmed, declined, maybe
                $table->string('actual_status')->nullable(); // attended, absent, excused
                $table->boolean('is_mismatch')->default(false);
                $table->text('note')->nullable(); // poznámka člena (omluvenka)
                $table->text('internal_note')->nullable(); // poznámka trenéra
                $table->timestamp('responded_at')->nullable(); // čas poslední odpovědi
                $table->json('metadata')->nullable();
                $table->timestamps();

                // Unikátní index pro user + událost
                $table->unique(['user_id', 'attendable_id', 'attendable_type'], 'user_attendance_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
