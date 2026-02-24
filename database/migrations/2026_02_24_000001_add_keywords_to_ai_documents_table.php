<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ai_documents', function (Blueprint $table): void {
            $table->json('keywords')->nullable()->after('content');
            $table->json('metadata')->nullable()->after('keywords');
        });
    }

    public function down(): void
    {
        Schema::table('ai_documents', function (Blueprint $table): void {
            $table->dropColumn(['keywords', 'metadata']);
        });
    }
};
