# Externí statistiky a AI Ingest Pipeline

Tento dokument slouží jako detailní technická specifikace pro implementaci stahování externích statistik (např. z webu ČBF). Obsahuje přehled databázových tabulek, modelů a rozhraní připravených pro automatizovaný import.

## 1. Databázová struktura

### 1.1 Základní sportovní entity

#### Tabulka: `seasons` (Model: `App\Models\Season`)
*Správa sezón pro filtrování zápasů a statistik.*
- `id`: Primary Key.
- `name`: String (např. "2024/2025").
- `is_active`: Boolean (označuje aktuálně probíhající sezónu).

#### Tabulka: `teams` (Model: `App\Models\Team`)
*Kategorie a týmy klubu.*
- `id`: Primary Key.
- `name`: Longtext (translatable JSON: `{"cs": "...", "en": "..."}`).
- `slug`: String (unique, např. "muzi-c").
- `category`: String (např. "senior", "youth").
- `description`: Longtext (translatable).

#### Tabulka: `opponents` (Model: `App\Models\Opponent`)
*Adresář soupeřů.*
- `id`: Primary Key.
- `name`: String.
- `city`: String (nullable).
- `logo`: String (reference na Media Library).

#### Tabulka: `matches` (Model: `App\Models\BasketballMatch`)
*Evidence zápasů, výsledků a externích metadat.*
- `id`: Primary Key.
- `season_id`: Foreign Key (`seasons`).
- `team_id`: Foreign Key (`teams`).
- `opponent_id`: Foreign Key (`opponents`, nullable).
- `match_type`: String (např. "league", "cup", "friendly").
- `scheduled_at`: DateTime.
- `location`: String (název haly/místa).
- `is_home`: Boolean.
- `status`: String ("planned", "completed", "cancelled", "postponed").
- `score_home`: Integer (nullable).
- `score_away`: Integer (nullable).
- `notes_internal`: Text (interní poznámka).
- `notes_public`: Longtext (translatable, veřejný komentář).
- **`metadata`**: JSON (klíčové pro import - např. `{"external_id": "12345", "source": "cbf"}`).

### 1.2 Hráči a profily

#### Tabulka: `player_profiles` (Model: `App\Models\PlayerProfile`)
*Rozšiřující data k uživateli, která se mění v čase.*
- `id`: Primary Key.
- `user_id`: Foreign Key (`users`, unique).
- `jersey_number`: String (aktuální číslo dresu).
- `preferred_jersey_number`: String.
- `position`: String (Enum `BasketballPosition`: PG, SG, SF, PF, C).
- `dominant_hand`: String (Enum `DominantHand`).
- **`license_number`**: String (klíčové pro párování s externím zdrojem / ID federace).
- `is_active`: Boolean.
- `valid_from` / `valid_to`: Date (historická platnost profilu).
- `primary_team_id`: Foreign Key (`teams`).
- **`metadata`**: JSON (doplňková data z importu).

### 1.3 Statistický systém

#### Tabulka: `statistic_sets` (Model: `App\Models\StatisticSet`)
*Definuje schéma pro konkrétní typ statistiky.*
- `id`: Primary Key.
- `name`: Longtext (translatable).
- `slug`: String (unique).
- `type`: String (Enum `StatisticType`: player, team, match).
- **`column_config`**: JSON (definice sloupců: `[{"key": "pts", "label": "Body"}, ...]`).
- `source_type`: String ("manual" nebo "external").

#### Tabulka: `statistic_rows` (Model: `App\Models\StatisticRow`)
*Samotná data statistik.*
- `id`: Primary Key.
- `statistic_set_id`: Foreign Key.
- `player_id`: Foreign Key (`users`, nullable).
- `team_id`: Foreign Key (`teams`, nullable).
- `basketball_match_id`: Foreign Key (`matches`, nullable).
- `season_id`: Foreign Key (`seasons`, nullable).
- **`values`**: JSON (payload dat: `{"pts": 12, "reb": 5}`).
- `source_metadata`: JSON (historie importu).

#### Tabulka: `external_stat_sources` (Model: `App\Models\ExternalStatSource`)
*Konfigurace a stav externích zdrojů dat.*
- `id`: Primary Key.
- `name`: String (název zdroje).
- `slug`: String (unikátní identifikátor zdroje, např. `czbasketball`).
- `source_url`: String (hlavní URL zdroje).
- `source_type`: String (`html_table`, `page_extract`, `api`).
- `extractor_config`: JSON (pravidla pro extrakci - např. CSS selektory).
- `mapping_config`: JSON (mapování na interní pole).
- `is_active`: Boolean.
- `last_run_at`: DateTime (poslední pokus o import).
- `last_status`: String (`success`, `error`).

#### Tabulka: `external_team_season_configs` (Model: `App\Models\ExternalTeamSeasonConfig`)
*Konfigurace synchronizace konkrétního týmu pro danou sezónu.*
- `id`: Primary Key.
- `source_key`: Identifikátor zdroje (např. `czbasketball`).
- `team_id`: Foreign Key (`teams`).
- `season_id`: Foreign Key (`seasons`).
- `external_team_id`: ID týmu v externím systému.
- `external_season_year`: Rok sezóny pro externí systém.
- `team_season_url`: URL profilu týmu (soupisky) na sezónu.
- `matches_list_url`: URL seznamu zápasů na sezónu.
- `is_enabled`: Boolean (povolení synchronizace).
- `last_synced_at`: DateTime (poslední pokus o synchronizaci).

#### Tabulka: `external_team_mappings` (Model: `App\Models\ExternalTeamMapping`)
*Propojení interních týmů s externími identitami ve zdrojích.*
- `id`: Primary Key.
- `source_key`: String (odpovídá `slug` v `external_stat_sources`).
- `team_id`: Foreign Key (`teams`).
- `external_team_id`: String (např. ID týmu v systému ČBF).
- `base_team_url`: String (URL profilu týmu na externím webu).
- `metadata`: JSON.

### 1.4 Auditní a logovací systém

#### Tabulka: `external_import_runs` (Model: `App\Models\ExternalImportRun`)
*Sleduje jednotlivé běhy synchronizace.*
- `id`: Primary Key.
- `source_key`: Zdroj (např. `czbasketball`).
- `run_type`: Typ (např. `matches_list`, `match_detail`).
- `status`: Stav (`queued`, `running`, `success`, `failed`, `partial_failed`).
- `started_at`, `finished_at`: Časové údaje.
- `extracted_count`, `imported_count`: Statistiky počtu záznamů.

#### Tabulka: `external_import_logs` (Model: `App\Models\ExternalImportLog`)
*Detailní log změn v rámci jednoho běhu importu.*
- `id`: Primary Key.
- `external_import_run_id`: Vazba na běh.
- `model_type`, `model_id`: Polymorfní vazba na změněný objekt (např. `BasketballMatch`, `StatisticRow`).
- `action`: Typ akce (`created`, `updated`, `skipped`, `error`).
- `old_values`: JSON s původními hodnotami (před změnou).
- `new_values`: JSON s novými hodnotami.
- `message`: Textový popis změny.

---

## 2. Architektura Importní Pipeline (AI Ingest)

Systém je navržen pro automatizované stahování a normalizaci dat pomocí AI.

### 2.1 Klíčová rozhraní (`app/Services/Stats/Contracts/`)
- `StatFetcherInterface`: Zajišťuje stažení HTML obsahu z URL.
- `StatExtractorInterface`: Vyhledá relevantní fragment v surovém obsahu.
- `StatNormalizerInterface`: Transformuje surová data (HTML/Text) do strukturovaného DTO pomocí AI/LLM.

### 2.2 DTO Třídy (`app/Services/Stats/DTO/`)
- `NormalizedTableDTO`: Reprezentuje celou tabulku (sloupce + řádky).
- `NormalizedRowDTO`: Reprezentuje jeden řádek dat (vazba na hráče, hodnoty).

---

## 3. Strategie Importu
1. **Párování hráčů**: Primárně přes `license_number` v `PlayerProfile`. Pokud není nalezeno, vytvoří se řádek s `row_label`.
2. **Párování zápasů**: Přes `metadata->external_id` v tabulce `matches`.
3. **Párování týmů**: Přes `slug` nebo externí ID v metadatech.
4. **AI Transformace**: StatNormalizer odesílá fragment HTML do LLM s promptem definovaným v `mapping_config` pro získání čistého JSONu.
5. **Vynucená synchronizace (Force/Fresh)**:
    - **Force mode**: Ignoruje kontrolu hashů obsahu a vždy provede stažení a zpracování dat. Vhodné při změně extraktoru nebo opravě chyb v normalizaci.
    - **Fresh mode**: Nejenže vynutí synchronizaci, ale před importem nových statistik (boxscoru) smaže všechna stávající data pro daný zápas v databázi. Tím se zajistí, že v DB nezůstane nic "starého", co už v externím zdroji není.

---

## 4. CLI Příkazy

Pro ruční spouštění synchronizace jsou k dispozici tyto příkazy:

- `php artisan stats:sync-team-season {teamSlug} {seasonName} [--force] [--fresh] [--sync]`: Synchronizuje celou sezónu týmu.
- `php artisan stats:sync-match {matchExternalId} {seasonName} {teamSlug} [--force] [--fresh] [--sync]`: Synchronizuje detail konkrétního zápasu.

Příznaky:
- `--force`: Ignoruje hash a vynutí stažení.
- `--fresh`: Smaže stávající data (statistiky) před importem.
- `--sync`: Spustí okamžitě v popředí (místo zařazení do fronty).
