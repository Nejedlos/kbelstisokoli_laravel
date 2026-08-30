<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Season extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'fine_no_response',
        'fine_no_show',
        'fine_unannounced_show',
        'fine_excused_show',
        'fine_missed_free_throw',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'fine_no_response' => 'decimal:2',
        'fine_no_show' => 'decimal:2',
        'fine_unannounced_show' => 'decimal:2',
        'fine_excused_show' => 'decimal:2',
        'fine_missed_free_throw' => 'decimal:2',
    ];

    /**
     * Normalizuje název sezóny do jednotného formátu YYYY/YYYY.
     */
    public static function normalizeName(?string $name): string
    {
        if (empty($name)) {
            return 'Neznámá sezóna';
        }

        $name = trim($name);

        // 1. Podpora YYYY - YYYY, YYYY-YYYY, YYYY / YYYY -> YYYY/YYYY
        if (preg_match('/(\d{4})\s*[\-\/]\s*(\d{4})/', $name, $matches)) {
            return $matches[1].'/'.$matches[2];
        }

        // 2. Podpora YYYY - YY, YYYY-YY, YYYY / YY -> YYYY/20YY
        if (preg_match('/^(\d{4})\s*[\-\/]\s*(\d{2})$/', $name, $matches)) {
            return $matches[1].'/20'.$matches[2];
        }

        // 3. Podpora samotného roku YYYY -> YYYY/YYYY+1
        if (preg_match('/^(\d{4})$/', $name, $matches)) {
            $year = (int) $matches[1];

            return $year.'/'.($year + 1);
        }

        // 4. Odstranění vícenásobných mezer kolem lomítka, pokud už tam je
        if (str_contains($name, '/')) {
            return preg_replace('/\s*\/\s*/', '/', $name);
        }

        return $name;
    }

    /**
     * Vrátí název sezóny, která by měla být aktuální podle data.
     * Sezóna začíná 1. září.
     */
    public static function getExpectedCurrentSeasonName(): string
    {
        $now = now();
        $year = $now->year;

        if ($now->month < 9) {
            // Jsme před zářím, sezóna začala loni
            return ($year - 1).'/'.$year;
        }

        // Jsme v září nebo později, sezóna začala letos
        return $year.'/'.($year + 1);
    }

    /**
     * Vrátí název předchozí sezóny.
     */
    public static function getPreviousSeasonName(): string
    {
        $now = now();
        $year = $now->year;

        if ($now->month < 9) {
            // Aktuální je (year-1)/year, předchozí je (year-2)/(year-1)
            return ($year - 2).'/'.($year - 1);
        }

        // Aktuální je year/(year+1), předchozí je (year-1)/year
        return ($year - 1).'/'.$year;
    }

    /**
     * Vrátí název předchozí sezóny z libovolného názvu.
     */
    public static function getPreviousSeasonNameFrom(string $name): string
    {
        if (str_contains($name, '/')) {
            [$year1, $year2] = explode('/', $name);
            if (is_numeric($year1) && is_numeric($year2)) {
                return ($year1 - 1).'/'.($year2 - 1);
            }
        }

        return $name;
    }

    /**
     * Zjistí, zda datum patří do této sezóny.
     * Sezóna začíná 1. září.
     */
    public function containsDate(CarbonInterface $date): bool
    {
        $normalized = self::normalizeName($this->name);
        if (! str_contains($normalized, '/')) {
            return false;
        }

        [$startYear, $endYear] = explode('/', $normalized);

        $start = Carbon::create((int) $startYear, 9, 1)->startOfDay();
        $end = Carbon::create((int) $endYear, 8, 31)->endOfDay();

        return $date->between($start, $end);
    }

    /**
     * Vrátí sezónu, do které patří dané datum.
     */
    public static function forDate(CarbonInterface $date): ?self
    {
        $year = (int) $date->format('Y');
        $month = (int) $date->format('m');

        // Sezóna začíná 1. září.
        $name = ($month >= 9) ? "$year/".($year + 1) : ($year - 1)."/$year";

        $season = self::where('name', $name)->first();
        if (! $season) {
            // Zkusíme zkrácený formát 2024/25
            $shortName = substr($name, 0, 5).substr($name, 7, 2);
            $season = self::where('name', $shortName)->first();
        }

        return $season;
    }

    public function matches(): HasMany
    {
        return $this->hasMany(BasketballMatch::class, 'season_id');
    }
}
