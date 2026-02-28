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
        if (! Schema::hasTable('finance_payments')) {
            Schema::create('finance_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
                $table->decimal('amount', 12, 2);
                $table->string('currency', 3)->default('CZK');
                $table->dateTime('paid_at');
                $table->string('payment_method')->default('bank_transfer'); // bank_transfer, cash, other
                $table->string('variable_symbol')->nullable();
                $table->string('transaction_reference')->nullable();
                $table->text('source_note')->nullable();
                $table->string('status')->default('recorded'); // recorded, confirmed, reversed
                $table->foreignId('recorded_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_payments');
    }
};
