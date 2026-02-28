<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class ClubCompetition extends Model
{
    use HasTranslations;

    public $translatable = ['name', 'description', 'metric_description', 'rules'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'metric_description',
        'season_id',
        'rules',
        'is_public',
        'status',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Záznamy v této soutěži.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(ClubCompetitionEntry::class)->orderByDesc('value');
    }

    /**
     * Získá leaderboard (seřazené unikátní účastníky s jejich celkovým skóre).
     * Toto je užitečné pro soutěže typu "incremental".
     */
    public function getLeaderboard()
    {
        // V praxi by zde byla komplexnější logika (group by player_id),
        // pro skeleton vracíme seřazené záznamy.
        return $this->entries()
            ->with(['player', 'team'])
            ->get();
    }
}
