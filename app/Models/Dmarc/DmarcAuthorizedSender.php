<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcAuthorizedSender extends Model
{
    protected $fillable = [
        'domain_name',
        'name',
        'description',
        'sender_type',
        'allowed_ips',
        'allowed_cidrs',
        'allowed_spf_domains',
        'allowed_dkim_domains',
        'allowed_dkim_selectors',
        'expected_header_from_domains',
        'notes',
        'is_active',
        'last_seen_at',
    ];

    protected $casts = [
        'allowed_ips' => 'json',
        'allowed_cidrs' => 'json',
        'allowed_spf_domains' => 'json',
        'allowed_dkim_domains' => 'json',
        'allowed_dkim_selectors' => 'json',
        'expected_header_from_domains' => 'json',
        'is_active' => 'boolean',
        'last_seen_at' => 'datetime',
    ];

    public function records()
    {
        return $this->hasMany(DmarcRecord::class, 'known_sender_id');
    }
}
