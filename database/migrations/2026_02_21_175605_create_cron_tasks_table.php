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
        Schema::create('cron_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('command'); // Artisan command or Job class
            $table->string('expression')->default('* * * * *'); // Cron expression
            $table->boolean('is_active')->default(true);
            $table->dateTime('last_run_at')->nullable();
            $table->string('last_status')->nullable(); // success, failed
            $table->text('last_error_message')->nullable();
            $table->integer('priority')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cron_tasks');
    }
};
