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
        Schema::create('feedback_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // bug|idea|feedback
            $table->string('severity')->nullable(); // low|medium|high
            $table->string('title', 120);
            $table->text('description');
            $table->text('steps')->nullable();
            $table->text('url');
            $table->string('route_name')->nullable();
            $table->string('locale')->nullable();
            $table->text('user_agent');
            $table->json('viewport')->nullable();
            $table->json('screen')->nullable();
            $table->string('timezone')->nullable();
            $table->string('source_area'); // public|member|admin
            $table->string('app_version')->nullable();
            $table->string('ip')->nullable();
            $table->string('status')->default('new'); // new|triaging|in_progress|resolved|wont_fix
            $table->text('admin_notes')->nullable();
            $table->string('screenshot_path')->nullable();
            $table->string('logs_path')->nullable();
            $table->string('network_path')->nullable();
            $table->string('clicks_path')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback_reports');
    }
};
