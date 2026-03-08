# Plánování synchronizace externích statistik

Tento dokument popisuje, jak je v systému nastaveno automatické stahování dat z webu `cz.basketball` pomocí Laravel Scheduleru.

## 1. Strategie synchronizace

Synchronizace je rozdělena do dvou režimů, aby byla zajištěna aktuálnost dat bez zbytečného přetěžování externího serveru.

### Baseline Sync (Noční synchronizace)
- **Kdy:** 1× denně v 03:30.
- **Rozsah:** Kompletní refresh soupisky a seznamu zápasů pro všechny sledované týmy v aktivní sezóně.
- **Účel:** Zachycení změn v soupiskách, nově naplánovaných zápasů nebo změn v termínech.

### Match-day & Post-match Sync (Průběžná synchronizace)
- **Kdy:** Každé 2 hodiny v čase 10:00 – 23:00.
- **Rozsah:** Rychlá kontrola seznamu zápasů a detailní synchronizace statistik (boxscore) pro zápasy odehrané v posledních 3 dnech.
- **Účel:** Rychlé stažení výsledků a statistik po skončení zápasu. Vzhledem k tomu, že statistiky jsou někdy doplňovány se zpožděním, kontrolují se zápasy zpětně po dobu 3 dnů.

## 2. Konfigurace

Nastavení se nachází v souboru `config/external_sources.php`.

```php
return [
    'enabled' => env('EXTERNAL_STATS_ENABLED', true),
    'czbasketball' => [
        'enabled' => true,
        'limits' => [
            'max_match_details_per_run' => 10, // Max. počet detailů zápasů v jedné dávce
            'recent_match_days' => 3,          // Kolik dní zpětně sledovat boxscore
        ],
        'schedule' => [
            'baseline_time' => '03:30',
            'match_day_frequency_minutes' => 120,
            'match_day_window' => ['10:00', '23:00'],
        ],
        'teams' => ['muzi-c', 'muzi-e'], // Slugy týmů pro automatickou synchronizaci
    ],
];
```

## 3. Implementované komponenty

### Joby
- `App\Jobs\Stats\ExternalStatsSchedulerJob`: Zastřešující job, který identifikuje aktivní sezónu a týmy a spouští synchronizaci.
- `App\Jobs\Stats\SyncTeamSeasonJob`: Synchronizuje soupisku a seznam zápasů.
- `App\Jobs\Stats\SyncMatchDetailJob`: Stahuje statistiky konkrétního zápasu.

### Scheduler
Registrace úloh probíhá v `bootstrap/app.php` pomocí metody `->withSchedule()`.

## 4. Bezpečnost a Idempotence

- **Content Hashing:** Každý stažený fragment (tabulka) je zahashován (SHA256). Pokud se hash shoduje s posledním úspěšným během, synchronizace je přeskočena (`skipped`).
- **Rate Limiting:** Počet detailních synchronizací zápasů (boxscore) v jednom běhu je omezen (výchozí limit 10), aby se předešlo blokaci IP adresy.
- **Timeouty a Retries:** HTTP požadavky mají nastavený timeout a v případě selhání sítě se automaticky opakují s exponenciálním zpožděním.

## 5. Nasazení na produkci

Na serveru musí běžet standardní Laravel cron, který spouští scheduler každou minutu:

```bash
* * * * * cd /cesta/k/projektu && php artisan schedule:run >> /dev/null 2>&1
```

Zároveň musí běžet worker pro zpracování fronty, protože synchronizace probíhá asynchronně:

```bash
php artisan queue:work --queue=default --timeout=60 --tries=3
```
