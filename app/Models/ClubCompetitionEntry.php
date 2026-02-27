<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClubCompetitionEntry extends Model
{
    protected $fillable = [
        'club_competition_id',
        'player_id',
        'label',
        'value',
        'value_type',
        'source_note',
        'basketball_match_id',
        'metadata',
    ];

    protected $casts = [
        'value' => 'float',
        'metadata' => 'array',
    ];

    public function competition(): BelongsTo
    {
        return $this->belongsTo(ClubCompetition::class, 'club_competition_id');
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(User::class, 'player_id');
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'club_competition_entry_team', 'entry_id', 'team_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(BasketballMatch::class, 'basketball_match_id');
    }
}
