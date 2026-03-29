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

### Použití v Blade
```blade
{{-- Automatický náhled tabulky pro konkrétní tým --}}
@livewire('public.standings-table', ['teamId' => $team->id, 'showFilters' => false, 'limit' => 5])

{{-- Detailní tabulka konkrétní soutěže v sidebaru --}}
@livewire('public.standings-table', ['competitionUrl' => $match->competition_url, 'compact' => true])
```

## Design a UX
- **Glassmorphism:** Tabulka využívá poloprůhledné pozadí s rozostřením (`backdrop-blur-xl`), což odpovídá modernímu vizuálnímu stylu projektu.
- **Zvýraznění:** Kbelské týmy jsou v tabulce automaticky zvýrazněny barvou brandu (`bg-primary/[0.03]`) a doplněny štítkem "Náš tým".
- **Animace:** Využívá třídu `animate-fade-in` pro plynulé načítání.
- **Responzivita:** Tabulka je vodorovně posuvná na mobilních zařízeních.

## Datový zdroj
Data se berou z tabulky `competition_standings`, která je plněna službou `StatisticSyncService`. Pro správné spárování s týmem musí existovat záznam v `external_team_season_configs` se shodným `competition_url`.

## Lokalizace
Komponenta je plně bilingvní. Texty jsou definovány v:
- `lang/{locale}/general.php` (společné texty jako Sezóna, Body, atd.)
- `lang/{locale}/teams.php` (titulek "Tabulka soutěže" v detailu týmu)
- `lang/{locale}/matches.php` (titulek widgetu v detailu zápasu)
