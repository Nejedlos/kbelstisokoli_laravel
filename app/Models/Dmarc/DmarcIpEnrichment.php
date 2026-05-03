<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcIpEnrichment extends Model
{
    protected $fillable = [
        'ip_address',
        'reverse_dns',
        'asn',
        'organization',
        'country',
        'first_seen_at',
        'last_seen_at',
        'times_seen',
        'last_lookup_at',
        'lookup_status',
        'raw_data',
    ];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'last_lookup_at' => 'datetime',
        'raw_data' => 'json',
        'times_seen' => 'integer',
    ];
}
