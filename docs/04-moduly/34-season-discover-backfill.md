# Season Backfill / Discover

Tento modul slouží k automatickému vyhledávání a doplňování chybějících konfigurací pro sezóny na webu `cz.basketball`. Je určen pro situace, kdy máme v systému vytvořené historické sezóny (např. 2017/2018), ale nemáme k nim nastaveno mapování na externí ID a parametry.

## 1. Princip fungování
Discovery proces prochází kombinace týmů a sezón, které jsou v systému "prázdné" (nemají zápasy, statistiky ani konfiguraci). Pro každou takovou kombinaci se pokouší najít správnou hodnotu parametru `y=YYYY` (rok začátku sezóny) na webu `cz.basketball`.

### Strategie hledání kandidátů:
- **Primární:** Pokud se sezóna jmenuje např. "2019/2020", prvním kandidátem je `y=2019`.
- **Fallback:** Pokud primární kandidát neuspěje, zkouší se okolní roky (±1 rok).
- **Rozsah:** Možnost ručně definovat rozsah let přes CLI.

## 2. Verifikace (Candidate Verifier)
Abychom předešli falešným nálezům (např. prázdná stránka s 200 OK), každý kandidát prochází verifikací:
1. **Soupiska:** Kontrola, zda na stránce týmu pro daný rok existuje tabulka s alespoň 3 hráči.
2. **Zápasy:** Kontrola, zda v seznamu zápasů existuje alespoň jeden záznam.
3. **Confidence Score:** Každý nález zvyšuje skóre důvěryhodnosti. Pokud skóre překročí prahovou hodnotu, je kandidát považován za vítěze.

## 3. Artisan Příkaz
Hlavním nástrojem pro hromadné vyhledávání je CLI příkaz:

```bash
php artisan stats:season-discover {teamSlug?} {seasonName?} [options]
```

### Parametry:
- `teamSlug`: Volitelný filtr pro konkrétní tým (např. `muzi-c`).
- `seasonName`: Volitelný filtr pro konkrétní sezónu (např. `2018/2019`).

### Volby (Options):
- `--years=2010..2025`: Definuje rozsah let k prohledání.
- `--dry-run`: Pouze vypíše nalezené konfigurace, ale nic neukládá.
- `--sync`: Po úspěšném nalezení a uložení konfigurace rovnou spustí synchronizaci dat.
- `--force`: Znovu prověří i sezóny, které již konfiguraci mají.

## 4. Administrace (Debug Panel)
V sekci **Debug / Operations** (Filament) je k dispozici karta **Season Backfill**, která zobrazuje počet aktuálně prázdných sezón a umožňuje spustit discovery proces jedním kliknutím.

## 5. Bezpečnost a Throttling
- **Delay:** Mezi jednotlivými požadavky na externí web je vložen krátký delay (0.5s - 1s), abychom předešli zablokování (rate limiting).
- **Audit:** Každý úspěšný discovery proces je zaznamenán v historii importů (`ExternalImportRun`).
- **Idempotence:** Pokud již validní konfigurace existuje, discovery ji standardně nepřepisuje (pokud není použit `--force`).

## 6. Troubleshooting
- **Nenalezen žádný rok:** Pokud discovery selže, může to být způsobeno změnou struktury webu nebo tím, že daný tým v dané sezóně v soutěžích ČBF nefiguroval.
- **Špatná subdoména:** Verifier automaticky zkouší subdomény `smo` a `www`. Pokud se změní doménová struktura, je nutné aktualizovat `SeasonYearCandidateVerifier`.

## Související soubory
- `App\Services\Stats\Sync\SeasonDiscoveryService` - Hlavní orchestrátor.
- `App\Services\Stats\Sync\SeasonYearCandidateVerifier` - Logika ověřování URL.
- `App\Services\Stats\Sync\SeasonDataStatusService` - Detekce prázdných dat.
- `App\Console\Commands\DiscoverSeasons` - CLI rozhraní.
