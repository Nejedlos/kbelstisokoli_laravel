<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('attendable_id');
            $table->string('attendable_type', 80);
            $table->string('kind', 24);
            $table->string('stage', 24);
            $table->string('status', 24)->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->longText('last_error')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'attendable_id', 'attendable_type', 'kind', 'stage'], 'attendance_email_delivery_unique');
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_email_deliveries');
    }
};
