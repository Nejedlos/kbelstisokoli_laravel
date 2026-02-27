<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\MorphMany;

use App\Traits\Auditable;
use Spatie\Translatable\HasTranslations;

class BasketballMatch extends Model
{
    use HasTranslations, Auditable;

    protected $table = 'matches';

    protected $fillable = [
        'team_id',
        'season_id',
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

    public function season(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function opponent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Opponent::class);
    }
}
