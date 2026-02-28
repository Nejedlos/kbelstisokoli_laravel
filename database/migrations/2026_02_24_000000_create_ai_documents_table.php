<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 50)->index();
            $table->string('source')->index();
            $table->string('title');
            $table->string('url')->nullable();
            $table->string('locale', 5)->default('cs')->index();
            $table->longText('content');
            $table->string('checksum', 64)->nullable()->index();
            $table->timestamps();
        });

        // Volitelný FULLTEXT index pro MySQL
        try {
            if (DB::getDriverName() === 'mysql') {
                $prefix = DB::getTablePrefix();
                DB::statement("ALTER TABLE {$prefix}ai_documents ADD FULLTEXT fulltext_title_content (title, content)");
            }
        } catch (Throwable $e) {
            // Ignorovat, pokud DB fulltext nepodporuje
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_documents');
    }
};
