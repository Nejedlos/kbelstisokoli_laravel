<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamEloRating extends Model
{
    protected $fillable = [
        'season_id',
        'team_key',
        'rating',
        'last_match_at',
    ];

    protected $casts = [
        'rating' => 'float',
        'last_match_at' => 'datetime',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get team key for our team.
     */
    public static function getInternalTeamKey(int $teamId): string
    {
        return "team_{$teamId}";
    }

    /**
     * Get team key for opponent.
     */
    public static function getOpponentKey(int $opponentId): string
    {
        return "opp_{$opponentId}";
    }
}
