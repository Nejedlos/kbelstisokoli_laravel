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
        // 1. Katalog legitimních odesílatelů
        Schema::create('dmarc_authorized_senders', function (Blueprint $table) {
            $table->id();
            $table->string('domain_name')->index(); // Doména, pro kterou je odesílatel autorizován
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('sender_type')->default('other'); // internal, hosting, smtp_provider, atd.
            $table->json('allowed_ips')->nullable();
            $table->json('allowed_cidrs')->nullable();
            $table->json('allowed_spf_domains')->nullable();
            $table->json('allowed_dkim_domains')->nullable();
            $table->json('allowed_dkim_selectors')->nullable();
            $table->json('expected_header_from_domains')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        // 2. IP Enrichment
        Schema::create('dmarc_ip_enrichments', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address')->unique();
            $table->string('reverse_dns')->nullable();
            $table->string('asn')->nullable();
            $table->string('organization')->nullable();
            $table->string('country')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->integer('times_seen')->default(0);
            $table->timestamp('last_lookup_at')->nullable();
            $table->string('lookup_status')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamps();
        });

        // 3. DNS Snapshots
        Schema::create('dmarc_dns_snapshots', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->index();
            $table->timestamp('checked_at');
            $table->text('dmarc_record')->nullable();
            $table->string('dmarc_policy')->nullable();
            $table->string('dmarc_subdomain_policy')->nullable();
            $table->integer('dmarc_pct')->nullable();
            $table->string('dmarc_adkim')->nullable();
            $table->string('dmarc_aspf')->nullable();
            $table->json('dmarc_rua')->nullable();
            $table->json('dmarc_ruf')->nullable();
            $table->text('spf_record')->nullable();
            $table->boolean('spf_exists')->default(false);
            $table->boolean('spf_multiple_records')->default(false);
            $table->json('warnings')->nullable();
            $table->json('recommendations')->nullable();
            $table->json('raw_dns')->nullable();
            $table->timestamps();
        });

        // 4. Alert Events
        Schema::create('dmarc_alert_events', function (Blueprint $table) {
            $table->id();
            $table->string('domain')->index();
            $table->string('source_ip')->index();
            $table->string('report_org')->nullable();
            $table->string('event_type')->index();
            $table->string('severity')->index();
            $table->integer('risk_score')->default(0);
            $table->string('fingerprint')->unique();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->integer('occurrences')->default(1);
            $table->timestamp('last_email_sent_at')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->text('resolution_note')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        // 5. Rozšíření existujících tabulek
        Schema::table('dmarc_records', function (Blueprint $table) {
            $table->foreignId('known_sender_id')->nullable()->constrained('dmarc_authorized_senders')->onDelete('set null');
            $table->json('analysis')->nullable();
            $table->string('severity')->nullable()->index();
            $table->integer('risk_score')->nullable()->index();
            $table->json('recommendations')->nullable();
            $table->timestamp('analyzed_at')->nullable()->index();
        });

        Schema::table('dmarc_reports', function (Blueprint $table) {
            $table->json('metadata')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dmarc_reports', function (Blueprint $table) {
            $table->dropColumn('metadata');
        });

        Schema::table('dmarc_records', function (Blueprint $table) {
            $table->dropForeign(['known_sender_id']);
            $table->dropColumn(['known_sender_id', 'analysis', 'severity', 'risk_score', 'recommendations', 'analyzed_at']);
        });

        Schema::dropIfExists('dmarc_alert_events');
        Schema::dropIfExists('dmarc_dns_snapshots');
        Schema::dropIfExists('dmarc_ip_enrichments');
        Schema::dropIfExists('dmarc_authorized_senders');
    }
};
