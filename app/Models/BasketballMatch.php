<?php

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasMatchResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Spatie\Translatable\HasTranslations;

class BasketballMatch extends Model
{
    use Auditable, HasTranslations, HasMatchResult;

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

    public function statisticRows(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StatisticRow::class, 'basketball_match_id')
            ->whereNotNull('player_id');
    }

    /**
     * Vrátí náhodnou motivační hlášku k odehranému zápasu na základě výsledku.
     */
    public function getPostMatchVibeAttribute(): string
    {
        if (!$this->has_score) {
            return __('motivational.post_match.fallback');
        }

        if ($this->is_win) {
            $quotes = __('motivational.post_match.win');
        } elseif ($this->is_loss) {
            $quotes = __('motivational.post_match.loss');
        } else {
            $quotes = __('motivational.post_match.draw');
        }

        return is_array($quotes) ? $quotes[array_rand($quotes)] : $quotes;
    }

    /**
     * Vrátí náhodnou motivační hlášku pro zápas na základě pravděpodobnosti výhry.
     */
    public function getMotivationalQuoteAttribute(): string
    {
        $winProb = $this->prediction?->probability_win ?? 0.5;
        $winChance = round($winProb * 100);

        if ($winChance < 35) {
            $quotes = __('motivational.pre_match.low');
        } elseif ($winChance > 65) {
            $quotes = __('motivational.pre_match.high');
        } else {
            $quotes = __('motivational.pre_match.medium');
        }

        return is_array($quotes) ? $quotes[array_rand($quotes)] : $quotes;
    }

    /**
     * Vrátí lidsky čitelný typ zápasu (mistrák, pohár, přátelák).
     */
    public function getMatchTypeLabelAttribute(): string
    {
        return match ($this->match_type) {
            'mistrovske' => 'mistrák',
            'poharove' => 'pohár',
            'pratelske' => 'přátelák',
            'TUR' => 'turnaj',
            default => $this->match_type ?? 'zápas',
        };
    }

    /**
     * Vrátí oficiální název našeho týmu (Sokol Kbely C, Sokol Kbely E atd.).
     * Pokouší se ho dohledat v ExternalTeamSeasonConfig.
     */
    public function getOfficialTeamNameAttribute(): string
    {
        // 1. Priorita: Je něco přímo v metadatech zápasu?
        if (isset($this->metadata['official_team_name'])) {
            return $this->metadata['official_team_name'];
        }

        // 2. Priorita: Dohledání v ExternalTeamSeasonConfig pro sezónu a náš tým
        $teamIds = $this->teams->pluck('id')->toArray();
        if (empty($teamIds) && $this->team_id) {
            $teamIds = [$this->team_id];
        }

        if (!empty($teamIds) && $this->season_id) {
            $config = ExternalTeamSeasonConfig::whereIn('team_id', $teamIds)
                ->where('season_id', $this->season_id)
                ->whereNotNull('team_name_in_source')
                ->first();

            if ($config) {
                return $config->team_name_in_source;
            }
        }

        // 3. Priorita: Název týmu z databáze (Spatie Translatable se postará o locale)
        // Předpokládáme, že náš název v DB (např. "Sokol Kbely E") je dostatečně oficiální
        // pokud nemáme název přímo ze zdroje.
        $team = $this->teams->first() ?? $this->team;
        if ($team && $team->name) {
            return (string) $team->name;
        }

        // Fallback: Brandingový název nebo "Sokoli"
        return app(\App\Services\BrandingService::class)->getSettings()['club_short_name'] ?? 'Sokoli';
    }

    /**
     * Vrátí oficiální název soupeře.
     * Aktuálně fallback na název z tabulky Opponent.
     */
    public function getOfficialOpponentNameAttribute(): string
    {
        if (isset($this->metadata['official_opponent_name'])) {
            return $this->metadata['official_opponent_name'];
        }

        return $this->opponent?->name ?? 'Soupeř';
    }

    /**
     * Vrátí čas srazu (vždy 15 minut před začátkem).
     */
    public function getMeetingAtAttribute(): ?\Illuminate\Support\Carbon
    {
        return $this->scheduled_at?->copy()->subMinutes(15);
    }

    /**
     * Vrátí informaci o barvě dresů (doma = bílá/světlá, venku = tmavá).
     */
    public function getJerseysInfoAttribute(): string
    {
        // V basketbalu je pravidlo: domácí hrají ve světlém (bílá), hosté v tmavém.
        return $this->is_home
            ? 'Bílá (Světlá)'
            : 'Tmavá';
    }
}
