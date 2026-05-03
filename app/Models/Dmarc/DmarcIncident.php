<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcIncident extends Model
{
    protected $fillable = [
        'record_id',
        'report_id',
        'domain',
        'source_ip',
        'severity',
        'title',
        'description',
        'recommended_action',
        'occurrences_count',
        'first_seen_at',
        'last_seen_at',
        'notified_at',
        'state',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'notified_at' => 'datetime',
        'occurrences_count' => 'integer',
    ];

    public function record()
    {
        return $this->belongsTo(DmarcRecord::class, 'record_id');
    }

    public function report()
    {
        return $this->belongsTo(DmarcReport::class, 'report_id');
    }
}
