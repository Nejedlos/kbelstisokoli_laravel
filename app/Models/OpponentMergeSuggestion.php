<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpponentMergeSuggestion extends Model
{
    protected $fillable = [
        'source_opponent_id',
        'target_opponent_id',
        'similarity',
        'status',
    ];

    public function sourceOpponent(): BelongsTo
    {
        return $this->belongsTo(Opponent::class, 'source_opponent_id');
    }

    public function targetOpponent(): BelongsTo
    {
        return $this->belongsTo(Opponent::class, 'target_opponent_id');
    }
}
