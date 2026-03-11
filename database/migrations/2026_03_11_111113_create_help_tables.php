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
        Schema::create('help_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('help_categories')->onDelete('set null');
            $table->text('name');
            $table->string('slug')->unique()->index();
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_customized')->default(false);
            $table->string('source_hash')->nullable();
            $table->timestamps();
        });

        Schema::create('help_articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('help_categories')->onDelete('cascade');
            $table->text('title');
            $table->string('slug')->unique()->index();
            $table->longText('content');
            $table->text('excerpt')->nullable();
            $table->text('search_keywords')->nullable();
            $table->text('audience_roles')->nullable();
            $table->integer('sort_order')->default(0)->index();
            $table->boolean('is_published')->default(false)->index();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_customized')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->string('source_hash')->nullable();
            $table->text('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('help_quick_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_article_id')->constrained('help_articles')->onDelete('cascade');
            $table->text('label');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('help_faqs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('help_article_id')->constrained('help_articles')->onDelete('cascade');
            $table->text('question');
            $table->text('answer');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('help_article_related', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained('help_articles')->onDelete('cascade');
            $table->foreignId('related_article_id')->constrained('help_articles')->onDelete('cascade');
            $table->primary(['article_id', 'related_article_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('help_article_related');
        Schema::dropIfExists('help_faqs');
        Schema::dropIfExists('help_quick_actions');
        Schema::dropIfExists('help_articles');
        Schema::dropIfExists('help_categories');
    }
};
