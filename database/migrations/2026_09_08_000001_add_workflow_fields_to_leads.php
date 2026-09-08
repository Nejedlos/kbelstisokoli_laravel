<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            return;
        }

        Schema::table('leads', function (Blueprint $table): void {
            if (! Schema::hasColumn('leads', 'responsible_user_id')) {
                $table->foreignId('responsible_user_id')->nullable()->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('leads', 'internal_notes')) {
                $table->text('internal_notes')->nullable();
            }

            if (! Schema::hasColumn('leads', 'next_contact_at')) {
                $table->dateTime('next_contact_at')->nullable();
            }

            if (! Schema::hasColumn('leads', 'assigned_at')) {
                $table->dateTime('assigned_at')->nullable();
            }

            if (! Schema::hasColumn('leads', 'status_changed_at')) {
                $table->dateTime('status_changed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Lead records contain personal data. Do not drop workflow history on rollback.
    }
};
