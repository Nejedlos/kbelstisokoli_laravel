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
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->timestamp('occurred_at')->useCurrent()->index();
                $table->string('category')->index();
                $table->string('event_key')->index();
                $table->string('action');
                $table->string('severity')->nullable()->default('info')->index();

                $table->unsignedBigInteger('actor_user_id')->nullable()->index();
                $table->string('actor_type')->nullable();

                $table->string('subject_type')->nullable()->index();
                $table->unsignedBigInteger('subject_id')->nullable()->index();
                $table->string('subject_label')->nullable();

                $table->string('route_name')->nullable();
                $table->text('url')->nullable();
                $table->string('ip_address')->nullable();
                $table->string('ip_hash')->nullable();
                $table->text('user_agent_summary')->nullable();
                $table->string('request_id')->nullable()->index();

                $table->longText('metadata')->nullable();
                $table->longText('changes')->nullable();

                $table->boolean('is_system_event')->default(false)->index();
                $table->string('source')->default('web')->index();

                $table->timestamps();

                $table->index(['subject_type', 'subject_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
