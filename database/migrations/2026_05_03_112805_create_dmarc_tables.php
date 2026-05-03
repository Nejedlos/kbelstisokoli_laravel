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
        Schema::create('dmarc_mailboxes', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('host')->default('mail.webglobe.cz');
            $table->integer('port')->default(993);
            $table->string('encryption')->default('ssl'); // ssl, tls, null
            $table->string('username')->nullable();
            $table->text('password')->nullable(); // Encrypted via casts
            $table->string('status')->default('active'); // active, disabled
            $table->timestamp('last_checked_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();
        });

        Schema::create('dmarc_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mailbox_id')->constrained('dmarc_mailboxes')->onDelete('cascade');
            $table->string('message_uid')->index();
            $table->string('attachment_filename');
            $table->string('attachment_sha256')->index();
            $table->string('org_name');
            $table->string('report_id')->index();
            $table->string('domain')->index();
            $table->timestamp('date_start')->nullable();
            $table->timestamp('date_end')->nullable();
            $table->json('policy_published_json')->nullable();
            $table->string('raw_xml_path')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->unique(['mailbox_id', 'message_uid', 'attachment_sha256'], 'dmarc_reports_unique_idx');
        });

        Schema::create('dmarc_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained('dmarc_reports')->onDelete('cascade');
            $table->string('source_ip')->index();
            $table->integer('count')->default(0);
            $table->string('disposition')->nullable(); // none, quarantine, reject
            $table->string('dkim_result')->nullable(); // pass, fail
            $table->string('spf_result')->nullable(); // pass, fail
            $table->boolean('dkim_aligned')->nullable();
            $table->boolean('spf_aligned')->nullable();
            $table->string('header_from')->nullable();
            $table->string('envelope_from')->nullable();
            $table->string('dkim_domain')->nullable();
            $table->string('spf_domain')->nullable();
            $table->string('status')->index(); // OK, Warning, Critical
            $table->text('recommended_action')->nullable();
            $table->timestamps();
        });

        Schema::create('dmarc_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('record_id')->constrained('dmarc_records')->onDelete('cascade');
            $table->foreignId('report_id')->constrained('dmarc_reports')->onDelete('cascade');
            $table->string('domain')->index();
            $table->string('source_ip')->index();
            $table->string('severity')->index(); // Critical, High, Medium
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('recommended_action')->nullable();
            $table->integer('occurrences_count')->default(1);
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->string('state')->default('open')->index(); // open, ack, resolved
            $table->timestamps();
        });

        Schema::create('dmarc_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mailbox_id')->constrained('dmarc_mailboxes')->onDelete('cascade');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('messages_found')->default(0);
            $table->integer('reports_processed')->default(0);
            $table->integer('errors_count')->default(0);
            $table->text('log')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dmarc_runs');
        Schema::dropIfExists('dmarc_incidents');
        Schema::dropIfExists('dmarc_records');
        Schema::dropIfExists('dmarc_reports');
        Schema::dropIfExists('dmarc_mailboxes');
    }
};
