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
        if (! Schema::hasTable('fine_templates')) {
            Schema::create('fine_templates', function (Blueprint $table) {
                $table->id();
                $table->longText('name'); // Spatie Translatable používá JSON v LONGTEXT
                $table->decimal('default_amount', 10, 2)->default(0);
                $table->string('unit')->nullable();
                $table->longText('description')->nullable();
                $table->longText('metadata')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fine_templates');
    }
};
