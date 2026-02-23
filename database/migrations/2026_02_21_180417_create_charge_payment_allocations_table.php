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
        if (!Schema::hasTable('charge_payment_allocations')) {
            Schema::create('charge_payment_allocations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('finance_charge_id')->constrained()->onDelete('cascade');
                $table->foreignId('finance_payment_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->dateTime('allocated_at');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('charge_payment_allocations');
    }
};
