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
        if (!Schema::hasTable('finance_charges')) {
            Schema::create('finance_charges', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('charge_type')->default('membership_fee'); // membership_fee, camp_fee, tournament_fee, other
                $table->decimal('amount_total', 12, 2);
                $table->string('currency', 3)->default('CZK');
                $table->date('due_date')->nullable();
                $table->date('period_from')->nullable();
                $table->date('period_to')->nullable();
                $table->string('status')->default('open'); // draft, open, partially_paid, paid, cancelled, overdue
                $table->boolean('is_visible_to_member')->default(true);
                $table->text('notes_internal')->nullable();
                $table->foreignId('created_by_id')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_charges');
    }
};
