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
        if (!Schema::hasTable('financial_tariffs')) {
            Schema::create('financial_tariffs', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->decimal('base_amount', 12, 2);
                $table->string('unit')->default('month'); // month, season
                $table->text('description')->nullable();
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
        Schema::dropIfExists('financial_tariffs');
    }
};
