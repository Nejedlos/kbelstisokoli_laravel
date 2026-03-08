# Season-Aware Mapping pro externí data (cz.basketball)

Tento dokument popisuje strategii mapování externích entit na interní modely s ohledem na sezónnost a strukturu webu cz.basketball.

## 1. Realita zdroje cz.basketball

Z analýzy (Audit v17) vyplývá, že:
- **Team ID** je stabilní a nemění se mezi sezónami (např. Sokol Kbely E = 7738).
- **Sezóna** se přepíná parametrem `?y=YYYY`, kde YYYY je rok začátku sezóny (např. `2025` pro 2025/26).
- **Soupisky a zápasy** jsou závislé na tomto parametru.

## 2. Architektura mapování

Abychom zajistili stabilitu dat i při změně externího ID (např. u zápasů, kde se ID může v čase lišit), používáme třívrstvý model.

### 2.1 `ExternalTeamMapping` (Stabilní)
Ukládá trvalou vazbu mezi naším týmem (např. `muzi-e`) a externím ID (`7738`). 
- **Účel:** Základní identifikace týmu bez ohledu na sezónu.
- **Klíčová pole:** `source_key`, `team_id`, `external_team_id`.

### 2.2 `ExternalTeamSeasonConfig` (Sezónní)
Konkrétní nastavení importu pro daný tým v dané sezóně.
- **Účel:** Drží URL pro seznam zápasů a soupisku pro konkrétní rok.
- **Klíčová pole:** `season_id`, `external_season_year` (parametr `y`), `matches_list_url`.

### 2.3 `ExternalEntityMapping` (Sezónní entity)
Mapování konkrétních hráčů, zápasů a soupeřů v rámci sezóny.
- **Účel:** Převod externích ID (`/zapas/123`, `/hrac/456`) na interní modely.
- **Stabilní identita (`identity_key`):** I když se externí ID změní, držíme si stabilní klíč (např. rodné číslo/licence u hráče, nebo složený klíč u zápasu).

## 3. Match Identity Key

Pro zápasy generujeme unikátní klíč pomocí `App\Support\MatchIdentityKey`:
`season_id + team_slug + date + is_home + opponent_normalized [+ round]`

**Důvod:** Zajišťuje, že i když se externí ID zápasu v různých systémech (SMO vs. hlavní cz.basketball) liší, interně budeme vědět, že jde o tentýž zápas.

## 4. Ruční mapování hráčů

Pokud hráč nemá v systému vyplněné `license_number` (které je hlavním párovacím klíčem), probíhá mapování takto:
1. **Prvotní import:** Systém vytvoří záznam v `external_entity_mappings` s nízkou důvěrou (`confidence`) na základě jména a roku narození.
2. **Administrace:** V administraci bude dostupný nástroj pro potvrzení/změnu mapování na existujícího uživatele.
3. **Persistence:** Po ručním spárování se aktualizuje `internal_id` v `ExternalEntityMapping`, čímž se vazba zafixuje pro všechny budoucí importy v dané sezóně.

## 5. Metadata zápasů (`matches.metadata`)

Model `BasketballMatch` ukládá do pole `metadata` tyto informace pro rychlou orientaci:
- `external.source`: "czbasketball"
- `external.external_team_id`: Externí ID týmu.
- `external.external_season_year`: Rok sezóny (y).
- `external.season_external_match_id`: ID konkrétního zápasu.
- `external.match_identity_key`: Vypočtený unikátní klíč.
- `external.last_synced_at`: Timestamp poslední synchronizace.
