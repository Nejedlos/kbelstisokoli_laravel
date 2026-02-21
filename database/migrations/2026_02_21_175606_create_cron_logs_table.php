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
        Schema::create('cron_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cron_task_id')->constrained()->onDelete('cascade');
            $table->dateTime('started_at');
            $table->dateTime('finished_at')->nullable();
            $table->string('status'); // running, success, failed
            $table->longText('output')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_logs');
    }
};
