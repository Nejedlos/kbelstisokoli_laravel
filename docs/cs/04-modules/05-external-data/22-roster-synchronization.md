# Synchronizace soupisky (Roster Synchronization)

Tento dokument popisuje proces synchronizace soupisek z externího zdroje `cz.basketball` a způsob, jakým systém nakládá s hráči, kteří dosud nejsou v interní databázi.

## 1. Přehled procesu

Synchronizace je zajištěna službou `App\Services\Stats\Sync\RosterSyncService`. Proces probíhá v následujících krocích:

1. **Stažení dat:** Pomocí `CzBasketballFetcher` se stáhne HTML stránka týmu pro danou sezónu.
2. **Extrakce:** `TeamRosterExtractor` vyhledá tabulku soupisky a převede ji na `NormalizedTableDTO`.
3. **Kontrola změn (Idempotence):** Porovná se SHA256 hash HTML fragmentu tabulky s posledním úspěšným během. Pokud se shodují, synchronizace se přeskočí.
4. **Zpracování hráčů:**
    - Pro každého hráče se hledá shoda v systému (viz sekce 2).
    - Pokud hráč neexistuje, vytvoří se "Ghost" uživatel.
    - Aktualizuje se příslušnost k týmu a příznak `is_on_roster`.
5. **Deaktivace:** Hráči, kteří již na externí soupisce nefigurují, jsou v pivot tabulce označeni jako `is_on_roster = false`.

## 2. Strategie párování hráčů

Při importu hráče se postupuje podle této priority:

1. **Mapování entit:** Hledá se v tabulce `external_entity_mappings` podle `source_key` ("czbasketball") a `external_id` (ID z URL hráče).
2. **Licenční číslo:** Pokud shoda přes mapování neexistuje, zkusí se najít `PlayerProfile` podle `license_number` (pokud se shoduje s externím ID).
3. **Vytvoření nového:** Pokud shoda není nalezena, systém vytvoří nového uživatele.

## 3. "Ghost" uživatelé

Pokud systém narazí na hráče, kterého nezná, vytvoří tzv. **Ghost uživatele**. Toto opatření umožňuje systému pracovat se statistikami i pro hráče, kteří ještě nemají v klubu založený účet.

### Vlastnosti Ghost uživatele:
- **Email:** Generován ve formátu `ghost_czbasketball_{external_id}@kbelstisokoli.cz`. Tento e-mail je unikátní a slouží jako technický identifikátor.
- **Heslo:** Náhodně generovaný dlouhý řetězec.
- **Stav:** `is_active = false`. Ghost uživatel se nemůže přihlásit do systému.
- **Metadata:** Obsahují příznak `is_ghost => true` a informace o původu.
- **Profil:** Automaticky se vytvoří `PlayerProfile` propojený s tímto uživatelem.

### Postup pro administrátora:
Když se skutečný hráč rozhodne do systému zaregistrovat nebo když administrátor získá jeho údaje:
1. Administrátor v administraci vyhledá Ghost uživatele.
2. Změní mu e-mail na skutečný.
3. Nastaví `is_active = true` (nebo nechá hráče provést reset hesla).
4. Mapování entit zůstane zachováno, takže historické statistiky se automaticky propojí.

## 4. Auditování

Každý běh synchronizace je zaznamenán v tabulce `external_import_runs`.
- Ukládá se počet extrahovaných, importovaných a přeskočených řádků.
- V metadatech jsou uložena případná varování (např. chybějící ID u hráče).
- Ukládá se snapshot HTML pro možnost zpětného debugování.

## 5. Čištění soupisky

Služba provádí automatickou údržbu `is_on_roster`. Pokud je hráč v databázi veden jako člen soupisky týmu A, ale v novém importu týmu A chybí, systém mu v pivot tabulce `player_profile_team` nastaví `is_on_roster = false`. Historie příslušnosti k týmu (vztah v pivotu) zůstává zachována, mění se pouze aktuální status pro danou sezónu.
