<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadRoutingSetting extends Model
{
    protected $fillable = ['default_responsible_user_id'];

    public function defaultResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'default_responsible_user_id');
    }
}
