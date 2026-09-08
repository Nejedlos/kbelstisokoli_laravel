<?php

namespace App\Models;

use App\Traits\HasSeo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Team extends Model
{
    use HasFactory, HasSeo, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'category',
        'primary_venue_id',
        'description',
    ];

    public $translatable = ['name', 'description'];

    public function matches(): BelongsToMany
    {
        return $this->belongsToMany(BasketballMatch::class, 'basketball_match_team', 'team_id', 'basketball_match_id');
    }

    public function legacyMatches(): HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'team_id');
    }

    public function trainings(): BelongsToMany
    {
        return $this->belongsToMany(Training::class, 'team_training');
    }

    public function clubEvents(): BelongsToMany
    {
        return $this->belongsToMany(ClubEvent::class, 'club_event_team');
    }

    public function primaryVenue(): BelongsTo
    {
        return $this->belongsTo(Venue::class, 'primary_venue_id');
    }

    /**
     * Hráči v týmu.
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(PlayerProfile::class, 'player_profile_team')
            ->withPivot(['role_in_team', 'is_primary_team', 'is_on_roster', 'active_from', 'active_to'])
            ->withTimestamps();
    }

    /**
     * Trenéři přiřazení k tomuto týmu.
     */
    public function coaches(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coach_team')
            ->withPivot(['email', 'phone'])
            ->withTimestamps();
    }

    /**
     * Aktivní trenéři.
     */
    public function activeCoaches(): BelongsToMany
    {
        return $this->coaches()->where('is_active', true);
    }

    /**
     * Aktivní hráči.
     */
    public function activePlayers(): BelongsToMany
    {
        return $this->players()
            ->where('player_profiles.is_active', true);
    }

    /**
     * Hráči na oficiální soupisce pro zápasy.
     */
    public function rosterPlayers(): BelongsToMany
    {
        return $this->activePlayers()
            ->wherePivot('is_on_roster', true);
    }

    /**
     * Externí mapování týmu (cz.basketball atd.)
     */
    public function externalMappings(): HasMany
    {
        return $this->hasMany(ExternalTeamMapping::class, 'team_id');
    }
}
