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
        Schema::table('matches', function (Blueprint $table) {
            $table->index('scheduled_at');
            $table->index('status');
        });

        Schema::table('trainings', function (Blueprint $table) {
            $table->index('starts_at');
            $table->index('ends_at');
        });

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                $table->index('starts_at');
                $table->index('ends_at');
            });
        }

        if (Schema::hasTable('club_events')) {
            Schema::table('club_events', function (Blueprint $table) {
                $table->index('starts_at');
                $table->index('ends_at');
            });
        }

        Schema::table('seasons', function (Blueprint $table) {
            $table->index('is_active');
        });

        Schema::table('teams', function (Blueprint $table) {
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['category']);
        });

        Schema::table('seasons', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });

        if (Schema::hasTable('club_events')) {
            Schema::table('club_events', function (Blueprint $table) {
                $table->dropIndex(['starts_at']);
                $table->dropIndex(['ends_at']);
            });
        }

        if (Schema::hasTable('events')) {
            Schema::table('events', function (Blueprint $table) {
                $table->dropIndex(['starts_at']);
                $table->dropIndex(['ends_at']);
            });
        }

        Schema::table('trainings', function (Blueprint $table) {
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['ends_at']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropIndex(['scheduled_at']);
            $table->dropIndex(['status']);
        });
    }
};
