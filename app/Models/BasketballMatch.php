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
     * Vrátí náhodnou pozitivní hlášku k odehranému zápasu.
     */
    public function getPostMatchVibeAttribute(): string
    {
        $quotes = [
            'Sokoli na hřišti, radost v srdci. Skvělý výkon celého týmu!',
            'Bojovnost, nasazení a týmový duch – to jsou naši kluci.',
            'Statistiky mluví jasně: naši borci do toho dali srdce.',
            'Každý bod se počítá, každý hráč je hrdina. Skvělá práce!',
            'Basketbal je o emocích a dnes jsme jich rozdali na rozdávání.',
            'Hrdost na naše barvy! Dnes jsme na hřišti nechali všechno.',
            'Týmová chemie, která funguje. Radost pohledět na naši hru.',
            'Sokolí křídla dnes nesla naše barvy vysoko. Skvělý zápas!',
        ];

        return $quotes[array_rand($quotes)];
    }

    /**
     * Vrátí náhodnou motivační hlášku pro zápas na základě pravděpodobnosti výhry.
     */
    public function getMotivationalQuoteAttribute(): string
    {
        $winProb = $this->prediction?->probability_win ?? 0.5;
        $winChance = round($winProb * 100);

        $lowQuotes = [
            "Pamatuj, že David taky porazil Goliáše. Basket se hraje na hřišti, ne v tabulkách!",
            "Statistiky jsou jen čísla. Srdce a bojovnost v datech nenajdeš.",
            "Čím těžší bitva, tím sladší vítězství. Pojďme jim ukázat sílu sokolů!",
            "Každý favorit jednou padne. Proč ne dneska proti nám?",
            "Máme rádi roli outsidera. Můžeme jen překvapit!",
            "Vítězství není o tom, kdo má víc bodů v Elo ratingu, ale kdo nechá na palubovce víc sil.",
            "Sázkové kanceláře nám nevěří, ale my víme, co v nás je!",
            "V basketu stačí jedna dobrá šňůra a všechno je jinak. Pojďme do toho!",
            "Tlak je na nich, my můžeme jenom šokovat svět!",
            "Papír snese všechno, ale palubovka nelže. Ukažme jim to!"
        ];

        $mediumQuotes = [
            "Tady se bude lámat chleba. Každý koš, každý doskok se počítá!",
            "Šance jsou vyrovnané, o vítězi rozhodne hlava a týmový duch.",
            "Jsme na dobré cestě. Stačí udržet koncentraci a věřit si.",
            "Dneska je to naše. Pojďme si pro tu výhru společně!",
            "Všechno máme ve vlastních rukou. Dneska to tam padne!",
            "Klíčem bude obrana. Když je zastavíme, vítězství nás nemine.",
            "Bude to boj o každý metr, ale my jsme připraveni!",
            "Soustředění na 100 % od první do poslední minuty.",
            "Týmový výkon dneska rozhodne. Jeden za všechny, všichni za Sokoly!",
            "Věřit si je polovina úspěchu. Tou druhou je dřina na hřišti."
        ];

        $highQuotes = [
            "Papírově jsme silnější, ale nesmíme nic podcenit. Pokora a nasazení!",
            "Máme na to je přejet. Pojďme od začátku diktovat tempo hry.",
            "Dneska musíme potvrdit roli favorita. Žádné výmluvy, jen výhra.",
            "Ukažme jim, proč jsme v tabulce tam, kde jsme.",
            "Sebevědomí je základ, ale bez dřiny to nepůjde. Pojďme do nich!",
            "Dneska hrajeme náš basket. Pokud udržíme kvalitu, nemají šanci.",
            "Dominance pod košem i na perimetru. To je náš dnešní cíl.",
            "Nedovolme jim ani na chvíli pomyslet na úspěch.",
            "Jsme rozjetí a nic nás nezastaví!",
            "Fanoušci čekají vítězství, pojďme jim ho doručit v plné parádě."
        ];

        if ($winChance < 35) {
            return $lowQuotes[array_rand($lowQuotes)];
        } elseif ($winChance > 65) {
            return $highQuotes[array_rand($highQuotes)];
        } else {
            return $mediumQuotes[array_rand($mediumQuotes)];
        }
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
