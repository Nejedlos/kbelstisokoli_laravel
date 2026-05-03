<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcAlertEvent extends Model
{
    protected $fillable = [
        'domain',
        'source_ip',
        'report_org',
        'event_type',
        'severity',
        'risk_score',
        'fingerprint',
        'first_seen_at',
        'last_seen_at',
        'occurrences',
        'last_email_sent_at',
        'is_resolved',
        'resolved_at',
        'resolution_note',
        'payload',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_email_sent_at' => 'datetime',
        'resolved_at' => 'datetime',
        'is_resolved' => 'boolean',
        'payload' => 'json',
        'occurrences' => 'integer',
        'risk_score' => 'integer',
    ];
}
