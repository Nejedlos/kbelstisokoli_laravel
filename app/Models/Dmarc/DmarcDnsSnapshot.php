<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcDnsSnapshot extends Model
{
    protected $fillable = [
        'domain',
        'checked_at',
        'dmarc_record',
        'dmarc_policy',
        'dmarc_subdomain_policy',
        'dmarc_pct',
        'dmarc_adkim',
        'dmarc_aspf',
        'dmarc_rua',
        'dmarc_ruf',
        'spf_record',
        'spf_exists',
        'spf_multiple_records',
        'warnings',
        'recommendations',
        'raw_dns',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
        'dmarc_pct' => 'integer',
        'dmarc_rua' => 'json',
        'dmarc_ruf' => 'json',
        'spf_exists' => 'boolean',
        'spf_multiple_records' => 'boolean',
        'warnings' => 'json',
        'recommendations' => 'json',
        'raw_dns' => 'json',
    ];
}
