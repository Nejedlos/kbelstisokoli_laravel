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
        Schema::create('ai_settings', function (Blueprint $table) {
            $table->id();

            // Aktivace a základní
            $table->boolean('enabled')->default(true);
            $table->boolean('use_database_settings')->default(false);
            $table->string('provider')->default('openai');

            // OpenAI připojení
            $table->text('openai_api_key')->nullable();
            $table->string('openai_base_url')->default('https://api.openai.com/v1');
            $table->string('openai_organization')->nullable();
            $table->string('openai_project')->nullable();
            $table->integer('openai_timeout_seconds')->default(90);
            $table->integer('openai_max_retries')->default(3);
            $table->boolean('openai_verify_ssl')->default(true);

            // Modely
            $table->string('default_chat_model')->default('gpt-4o-mini');
            $table->string('analyze_model')->default('gpt-4o');
            $table->string('fast_model')->default('gpt-4o-mini');
            $table->string('embeddings_model')->default('text-embedding-3-small');
            $table->longText('model_presets')->nullable();

            // Chování
            $table->float('temperature')->default(0.7);
            $table->float('top_p')->default(1.0);
            $table->integer('max_output_tokens')->default(2000);
            $table->text('system_prompt_default')->nullable();
            $table->text('system_prompt_search')->nullable();

            // Cache a výkon
            $table->boolean('cache_enabled')->default(true);
            $table->integer('cache_ttl_seconds')->default(3600);

            // Debug a logování
            $table->boolean('debug_enabled')->default(false);
            $table->boolean('debug_log_requests')->default(false);
            $table->boolean('debug_log_responses')->default(false);
            $table->boolean('debug_log_to_database')->default(true);
            $table->integer('retention_days')->default(30);

            $table->timestamps();
        });

        Schema::create('ai_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('context')->nullable(); // admin_search, member_ai, test_prompt, atd.
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('success'); // success, error
            $table->text('prompt_preview')->nullable();
            $table->text('response_preview')->nullable();
            $table->integer('latency_ms')->nullable();
            $table->longText('token_usage')->nullable(); // input, output, total
            $table->text('error_message')->nullable();
            $table->longText('metadata')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_request_logs');
        Schema::dropIfExists('ai_settings');
    }
};
