<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('lead_routing_settings')) {
            return;
        }

        Schema::create('lead_routing_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('default_responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Preserve routing configuration rather than risking notification loss on rollback.
    }
};
