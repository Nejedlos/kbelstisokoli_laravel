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
        if (Schema::hasTable('events') && !Schema::hasTable('club_events')) {
            Schema::rename('events', 'club_events');
        }

        Schema::table('club_events', function (Blueprint $table) {
            if (!Schema::hasColumn('club_events', 'event_type')) {
                $table->string('event_type')->default('other')->after('title'); // social, meeting, camp, volunteer, other
            }
            if (!Schema::hasColumn('club_events', 'team_id')) {
                $table->foreignId('team_id')->nullable()->after('event_type')->constrained()->onDelete('set null');
            }
            if (!Schema::hasColumn('club_events', 'rsvp_enabled')) {
                $table->boolean('rsvp_enabled')->default(true)->after('is_public');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('club_events', function (Blueprint $table) {
            $table->dropForeign(['team_id']);
            $table->dropColumn(['event_type', 'team_id', 'rsvp_enabled']);
        });

        Schema::rename('club_events', 'events');
    }
};
