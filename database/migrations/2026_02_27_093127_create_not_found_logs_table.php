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
        Schema::create('not_found_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url')->index();
            $table->string('referer')->nullable()->index();
            $table->string('user_agent')->nullable();
            $table->string('ip_address')->nullable();
            $table->unsignedBigInteger('hits_count')->default(1);
            $table->timestamp('last_seen_at')->useCurrent();
            $table->enum('status', ['pending', 'redirected', 'ignored'])->default('pending');
            $table->foreignId('redirect_id')->nullable()->constrained('redirects')->onDelete('set null');
            $table->boolean('is_ignored')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('not_found_logs');
    }
};
