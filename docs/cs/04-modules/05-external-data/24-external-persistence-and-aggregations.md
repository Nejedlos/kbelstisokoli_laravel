# Perzistence externích dat a agregace statistik

Tento dokument popisuje způsob ukládání dat z externích zdrojů (zejména `cz.basketball`) do interního datového modelu a následné výpočty sezónních souhrnů.

## 1. Identita a párování entit

### 1.1 Soupeři (Opponents)
Soupeři jsou identifikováni primárně kombinací **jména** a **města**. 
- Systém provádí upsert (vytvoření nebo aktualizaci) v `OpponentSyncService`.
- V poli `metadata` se ukládá `source_key` (např. `czbasketball`), `last_seen_at` a seznam variant jmen (`external_name_variants`), pod kterými byl soupeř v externích datech nalezen.

### 1.2 Zápasy (BasketballMatch)
Zápasy jsou identifikovány pomocí `MatchIdentityKey`, který kombinuje:
- `season_id`
- `team.slug`
- `scheduled_at` (datum)
- `is_home` (Boolean)
- `opponent_name` (Slugified)
- `round` (nepovinné)

Tento klíč zajišťuje, že i při změně externího ID (např. při přesunu zápasu na jiný termín nebo při chybě v externím systému) dokážeme zápasy v naší databázi stabilně sledovat. Klíč je uložen v `matches.metadata->match_identity_key`.

### 1.3 Hráči (Player Pairing)
Párování hráčů probíhá v několika krocích:
1. **External Mapping:** Vyhledání v `external_entity_mappings` podle `external_id` (např. ID z URL `/hrac/12345`).
2. **License Number:** Pokud mapping neexistuje, zkusí se párování přes `player_profiles.license_number`.
3. **Ghost User:** Pokud hráč není nalezen, je vytvořen "Ghost" uživatel (neaktivní, s placeholder e-mailem), aby bylo možné k němu ihned ukládat statistiky.

## 2. Statistické sady (Statistic Sets)

Pro externí importy používáme tři fixní sady statistik:

| Slug | Název | Typ | Rozsah (Scope) |
| :--- | :--- | :--- | :--- |
| `match-boxscore-external` | Statistiky zápasu | `match` | `match` |
| `player-season-summary-external` | Sezónní souhrn hráče | `player` | `season` |
| `team-season-summary-external` | Sezónní souhrn týmu | `team` | `season` |

### 2.1 Kanonické klíče (Canonical Keys)
V rámci boxscoru (`match-boxscore-external`) používáme tyto sjednocené klíče:
- `pts`: Body
- `minutes`: Minuty na hřišti
- `fg2_made` / `fg2_att`: 2-bodové koše (proměněné / vystřelené)
- `fg3_made` / `fg3_att`: 3-bodové koše (proměněné / vystřelené)
- `ft_made` / `ft_att`: Trestné hody (proměněné / vystřelené)
- `fouls`: Osobní chyby
- `fouls_drawn`: Získané fauly (F+)
- `assists`: Asistence
- `rebounds`: Doskoky (celkem)
- `steals`: Zisky
- `turnovers`: Ztráty
- `blocks`: Bloky
- `plus_minus`: +/- bilance
- `efficiency`: VAL (index užitečnosti)

## 3. Agregace a přepočty

### 3.1 Sezónní souhrn hráče
Po každém importu statistik zápasu (boxscore) se automaticky přepočítá souhrn pro dotčené hráče v dané sezóně.
- **gp (Games Played):** Počet řádků v boxscoru pro daného hráče a sezónu.
- **ppg (Points Per Game):** Celkové body / gp.
- **fg2_pct, fg3_pct, ft_pct:** Úspěšnost střelby vypočtená ze sumy `made` a `att`.

### 3.2 Sezónní souhrn týmu
Agreguje data ze všech odehraných zápasů (`status = completed`) daného týmu v sezóně.
- **gp:** Počet odehraných zápasů.
- **wins / losses:** Bilance výher a proher.
- **pts_for / pts_against:** Skóre pro a proti.
- **pts_avg:** Průměrný počet bodů na zápas.

## 4. Debugování a audit
Všechny importy statistických řádků (`StatisticRow`) ukládají v poli `source_metadata` informaci o zdroji (`source: czbasketball`), externí ID zápasu a hráče a čas scrapování. To umožňuje zpětnou dohledatelnost dat v případě nesrovnalostí.
