<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcRun extends Model
{
    protected $fillable = [
        'mailbox_id',
        'started_at',
        'finished_at',
        'messages_found',
        'reports_processed',
        'errors_count',
        'log',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'messages_found' => 'integer',
        'reports_processed' => 'integer',
        'errors_count' => 'integer',
    ];

    public function mailbox()
    {
        return $this->belongsTo(DmarcMailbox::class, 'mailbox_id');
    }
}
