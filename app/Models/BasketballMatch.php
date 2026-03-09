<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class BasketballMatch extends Model
{
    use Auditable, HasTranslations;

    protected $table = 'matches';

    protected $fillable = [
        'match_type',
        'season_id',
        'team_id',
        'opponent_id',
        'scheduled_at',
        'location',
        'is_home',
        'status',
        'score_home',
        'score_away',
        'notes_internal',
        'notes_public',
        'metadata',
    ];

    public $translatable = ['notes_public'];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'is_home' => 'boolean',
        'score_home' => 'integer',
        'score_away' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Docházka (dostupnost) k tomuto zápasu.
     */
    public function attendances(): MorphMany
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    /**
     * Záznamy docházky s rozporem.
     */
    public function mismatches(): MorphMany
    {
        return $this->attendances()->where('is_mismatch', true);
    }

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'basketball_match_team', 'basketball_match_id', 'team_id');
    }

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function opponent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Opponent::class);
    }

    public function prediction(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MatchPrediction::class, 'basketball_match_id');
    }
}
