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
