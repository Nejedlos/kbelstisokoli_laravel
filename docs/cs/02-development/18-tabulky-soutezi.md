# Tabulky soutěží (Standings)

Tento modul zajišťuje zobrazení aktuálních tabulek soutěží, ve kterých působí týmy Kbelští sokoli. Data jsou synchronizována z externích zdrojů (např. cz.basketball) do modelu `CompetitionStanding`.

## Livewire Komponenta: `Public.StandingsTable`

Komponenta `App\Livewire\Public\StandingsTable` je navržena pro znovupoužitelnost na frontendu.

### Parametry
- `seasonId` (int, null): ID sezóny (výchozí je aktivní sezóna).
- `teamId` (int, null): ID týmu pro filtraci tabulek, ve kterých tento tým hraje.
- `competitionUrl` (string, null): URL konkrétní soutěže pro zobrazení jedné specifické tabulky.
- `showFilters` (bool, true): Zda zobrazit výběr sezóny a týmu.
- `limit` (int, null): Omezení počtu zobrazených řádků (vhodné pro náhledy).
- `compact` (bool, false): Kompaktní režim zobrazení (méně sloupců, vhodné do sidebarů).
- `configuredOnly` (bool, false): Omezí výpis na soutěžní URL povolené pro tabulky z `external_team_season_configs` pro vybranou sezónu. Způsobilá je konfigurace s `is_enabled=true` nebo s `metadata.standings_enabled=true`. Homepage používá hodnotu `true`, aby nezobrazila osiřelé nebo historické tabulky.

### Použití v Blade
```blade
{{-- Automatický náhled tabulky pro konkrétní tým --}}
@livewire('public-standings-table', ['teamId' => $team->id, 'showFilters' => false, 'limit' => 5])

{{-- Detailní tabulka konkrétní soutěže v sidebaru --}}
@livewire('public-standings-table', ['competitionUrl' => $match->competition_url, 'compact' => true])
```

## Design a UX
- **Glassmorphism:** Tabulka využívá poloprůhledné pozadí s rozostřením (`backdrop-blur-xl`), což odpovídá modernímu vizuálnímu stylu projektu.
- **Zvýraznění:** Kbelské týmy jsou v tabulce automaticky zvýrazněny barvou brandu (`bg-primary/[0.03]`) a doplněny štítkem "Náš tým".
- **Animace:** Využívá třídu `animate-fade-in` pro plynulé načítání.
- **Responzivita:** Tabulka je vodorovně posuvná na mobilních zařízeních.

## Datový zdroj
Data se berou z tabulky `competition_standings`, která je plněna službou `StatisticSyncService`. Pro správné spárování s týmem musí existovat záznam v `external_team_season_configs` se shodným `competition_url`.

Výběr konfigurací pro tabulky centralizuje `StandingsConfigService`. Příznak `metadata.standings_enabled` umožní pravidelně stahovat jen tabulku soutěže, zatímco `is_enabled=false` dál blokuje plnou synchronizaci soupisky, hráčů a zápasů. Filtrace příznaku probíhá nad Eloquent kolekcí, protože `metadata` je přenosně uložené jako `longText`; nepoužívají se databázově specifické JSON operátory.

Příkaz `stats:sync-standings` synchronizuje všechny takto způsobilé URL aktivní sezóny. Databázový plánovač jej spouští každou hodinu ve 45. minutě.

Pražský portál PBasket běží na stejné platformě CZ.BASKETBALL. Jeho adresy `https://pbasket.cz.basketball/tym/...` a `https://pbasket.cz.basketball/soutez/...` proto používají stávající `source_key=czbasketball`, fetcher, extraktory i synchronizační služby; nevytváří se paralelní importér.

## Lokalizace
Komponenta je plně bilingvní. Texty jsou definovány v:
- `lang/{locale}/general.php` (společné texty jako Sezóna, Body, atd.)
- `lang/{locale}/teams.php` (titulek "Tabulka soutěže" v detailu týmu)
- `lang/{locale}/matches.php` (titulek widgetu v detailu zápasu)
