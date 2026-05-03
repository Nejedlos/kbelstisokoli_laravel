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
        'status',
        'recommended_action',
    ];

    protected $casts = [
        'dkim_aligned' => 'boolean',
        'spf_aligned' => 'boolean',
        'count' => 'integer',
    ];

    public function report()
    {
        return $this->belongsTo(DmarcReport::class, 'report_id');
    }

    public function incidents()
    {
        return $this->hasMany(DmarcIncident::class, 'record_id');
    }
}
