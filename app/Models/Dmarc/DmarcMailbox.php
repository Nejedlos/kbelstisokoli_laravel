<?php

namespace App\Models\Dmarc;

use Illuminate\Database\Eloquent\Model;

class DmarcMailbox extends Model
{
    protected $fillable = [
        'email',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'status',
        'last_checked_at',
        'last_error',
    ];

    protected $casts = [
        'password' => 'encrypted',
        'last_checked_at' => 'datetime',
    ];

    public function reports()
    {
        return $this->hasMany(DmarcReport::class, 'mailbox_id');
    }

    public function runs()
    {
        return $this->hasMany(DmarcRun::class, 'mailbox_id');
    }
}
