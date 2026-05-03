<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcRecord extends Model
{
    protected $fillable = [
        'report_id',
        'source_ip',
        'count',
        'disposition',
        'dkim_result',
        'spf_result',
        'dkim_aligned',
        'spf_aligned',
        'header_from',
        'envelope_from',
        'dkim_domain',
        'spf_domain',
        'known_sender_id',
        'analysis',
        'severity',
        'risk_score',
        'recommendations',
        'analyzed_at',
        'status',
        'recommended_action',
    ];

    protected $casts = [
        'dkim_aligned' => 'boolean',
        'spf_aligned' => 'boolean',
        'count' => 'integer',
        'risk_score' => 'integer',
        'analysis' => 'json',
        'recommendations' => 'json',
        'analyzed_at' => 'datetime',
    ];

    public function report()
    {
        return $this->belongsTo(DmarcReport::class, 'report_id');
    }

    public function knownSender()
    {
        return $this->belongsTo(DmarcAuthorizedSender::class, 'known_sender_id');
    }

    public function incidents()
    {
        return $this->hasMany(DmarcIncident::class, 'record_id');
    }
}
