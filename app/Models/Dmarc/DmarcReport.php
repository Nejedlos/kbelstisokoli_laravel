<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcReport extends Model
{
    protected $fillable = [
        'mailbox_id',
        'message_uid',
        'attachment_filename',
        'attachment_sha256',
        'org_name',
        'report_id',
        'domain',
        'date_start',
        'date_end',
        'policy_published_json',
        'raw_xml_path',
        'received_at',
        'metadata',
    ];

    protected $casts = [
        'policy_published_json' => 'json',
        'metadata' => 'json',
        'date_start' => 'datetime',
        'date_end' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function mailbox()
    {
        return $this->belongsTo(DmarcMailbox::class, 'mailbox_id');
    }

    public function records()
    {
        return $this->hasMany(DmarcRecord::class, 'report_id');
    }

    public function incidents()
    {
        return $this->hasMany(DmarcIncident::class, 'report_id');
    }
}
