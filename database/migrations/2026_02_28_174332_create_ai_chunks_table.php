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
        Schema::create('ai_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ai_document_id')->constrained('ai_documents')->cascadeOnDelete();
            $table->string('section')->index();
            $table->integer('chunk_index');
            $table->text('chunk_text');
            $table->char('chunk_hash', 64);
            $table->longText('embedding')->nullable();
            $table->integer('token_estimate')->nullable();
            $table->timestamps();

            $table->index(['section', 'ai_document_id']);
            $table->index(['ai_document_id', 'chunk_index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_chunks');
    }
};
