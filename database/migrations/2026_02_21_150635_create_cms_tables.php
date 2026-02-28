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
        // 1. Post Categories
        if (! Schema::hasTable('post_categories')) {
            Schema::create('post_categories', function (Blueprint $table) {
                $table->id();
                $table->longText('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 2. Posts (Novinky)
        if (! Schema::hasTable('posts')) {
            Schema::create('posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->nullable()->constrained('post_categories')->onDelete('set null');
                $table->longText('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable(); // Perex
                $table->longText('content')->nullable(); // Bude později využívat bloky
                $table->string('status')->default('draft'); // draft, published
                $table->timestamp('publish_at')->nullable();
                $table->string('featured_image')->nullable(); // Reference na soubor (budoucí Media Library)
                $table->timestamps();
            });
        }

        // 3. Pages (Stránky)
        if (! Schema::hasTable('pages')) {
            Schema::create('pages', function (Blueprint $table) {
                $table->id();
                $table->longText('title');
                $table->string('slug')->unique();
                $table->longText('content')->nullable(); // Připraveno na blokový obsah
                $table->string('status')->default('draft'); // draft, published
                $table->timestamps();
            });
        }

        // 4. Menus
        if (! Schema::hasTable('menus')) {
            Schema::create('menus', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('location')->unique()->nullable(); // Identifikátor pro použití v šablonách (např. 'header', 'footer')
                $table->timestamps();
            });
        }

        // 5. Menu Items
        if (! Schema::hasTable('menu_items')) {
            Schema::create('menu_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('menu_id')->constrained()->onDelete('cascade');
                $table->foreignId('parent_id')->nullable()->constrained('menu_items')->onDelete('cascade');
                $table->longText('label');
                $table->string('url')->nullable();
                $table->string('route_name')->nullable();
                $table->string('target')->default('_self'); // _self, _blank
                $table->unsignedInteger('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // 6. SEO Metadata (Elegantní polymorfní řešení)
        if (! Schema::hasTable('seo_metadatas')) {
            Schema::create('seo_metadatas', function (Blueprint $table) {
                $table->id();
                $table->morphs('seoable');
                $table->longText('title')->nullable();
                $table->longText('description')->nullable();
                $table->string('keywords')->nullable();
                $table->longText('og_title')->nullable();
                $table->longText('og_description')->nullable();
                $table->string('og_image')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seo_metadatas');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('post_categories');
    }
};
