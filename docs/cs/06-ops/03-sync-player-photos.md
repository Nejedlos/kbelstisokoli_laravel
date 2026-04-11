# Sync hráčských fotek (jen fotky)

Tento nástroj stáhne a uloží fotografie hráčů bez synchronizace statistik. Umí projít všechny povolené týmy a jejich sezóny z `ExternalTeamSeasonConfig`, případně lze omezit pouze na vybraný tým/sezónu. Respektuje rate‑limit a podporuje přepsání existujících fotek.

**Novinka (v1.3):** Pokud pro zadaný tým a sezónu neexistuje v databázi žádná konfigurace (`ExternalTeamSeasonConfig`), ale tým má v systému mapování pro `czbasketball`, systém se pokusí tuto konfiguraci **automaticky vytvořit** a pokračovat v synchronizaci.

## Příkaz

```bash
php artisan app:sync-player-photos [team] [season] [--force] [--delay=] [--per-player-delay-ms=] [--batch-size=] [--matches] -n
```

### Argumenty (Volitelné)
- `team` – Slug týmu (např. `muzi-e`) nebo interní ID; pokud není zadáno, projdou se všechny povolené konfigurace.
- `season` – Název sezóny (např. `2025/2026`) nebo interní ID; pokud není zadáno, projdou se všechny povolené sezóny týmu.

### Parametry (Volitelné)
- `--team_id=` – *Zastaralé* (použijte argument); interní ID týmu.
- `--season_id=` – *Zastaralé* (použijte argument); interní ID sezóny.
- `--force` – stáhne a přepíše fotky i tam, kde už byla tato konkrétní `source_url` uložena.
- `--delay=` – pauza mezi konfiguracemi tým×sezóna v sekundách (default 1s).
- `--per-player-delay-ms=` – pauza mezi hráči v milisekundách (default 200ms).
- `--batch-size=` – zpracuje pouze prvních N konfigurací (užitečné pro testování).
- `--matches` – projde i sekce "Nejlepší hráči" ve všech odehraných zápasech daného týmu a sezóny (stáhne fotky i pro soupeře).

## Vylepšená stabilita stahování (v1.2)
Příkaz a podkladová služba `PlayerSyncService` nyní obsahují robustní mechanismus pro stahování fotek z `cz.basketball`:
1. **Metadata:** Při stahování se k fotografii ukládá `season_id` a `team_id`. To umožňuje zobrazovat v detailech zápasů dobové fotografie (např. hráče jako dítě v sezóně 2015).
2. **Prioritizace:** Frontend (`match-detail.blade.php`) a administrace se pokouší najít fotografii pro konkrétní sezónu. Pokud není nalezena, použije se nejnovější dostupná.
3. **Přímé stahování:** Pokud selže standardní endpoint `min.php` (často vrací 404 pro starší fotky), systém se pokusí stáhnout soubor přímo z `cbf.cz`.
4. **Fallback na detail:** Pokud ani přímý odkaz nefunguje, systém navštíví profilovou stránku hráče a extrahuje aktuální URL fotky odtud.
5. **Ošetření chyb:** Chyba při stahování jedné fotky (např. 404) nezastaví celý proces synchronizace. Všechny neúspěchy jsou zaznamenány v logu.
6. **Průběh:** Příkaz nyní zobrazuje Progress Bar pro přehled o celkovém postupu přes všechny týmy.

## Chování
- Základem je soupisková stránka `team_season_url`. Pokud řádek soupisky obsahuje obrázek, stáhne se přímo tento zdroj.
- Pokud je aktivní příznak `--matches`, příkaz projde všechny zápasy v databázi pro daný tým a sezónu, vytáhne z jejich metadat nejlepší hráče (včetně soupeřů) a stáhne jejich fotografie přímo na disk do `uploads/opponents`.
- U našich hráčů se při `--matches` nadále vytvářejí/hledají "ghost" profily a fotka se ukládá do MediaLibrary. U soupeřů se v databázi nic nevytváří.
- Pokud řádek soupisky fotku neobsahuje, příkaz spadne na fallback: načte detail hráče `https://cz.basketball/hrac/{externalId}` a pokusí se získat `photo_url` tam.
- Bez `--force`:
  - Fotka se uloží pouze pokud pro stejné `source_url` ještě není v kolekci `player_photos` u daného uživatele.
  - Pokud je ze sezóny jiná `source_url`, uloží se jako nový záznam (různé sezóny mohou mít jiné fotky).
- S `--force`:
  - Pokud již existuje médium se stejným `source_url`, bude smazáno a fotka se stáhne znovu.

## Ukládání souborů
- Používá se `Spatie Media Library` a vlastní `CustomPathGenerator`.
- Disk: `MEDIA_DISK`/`UPLOADS_DISK` (výchozí `public_path`).
- Cesta: `public/uploads/user/{user_id}/player_photos/{media_id}`.
  - Na produkci (Webglobe): `/home/html/kbelstisokoli.cz/public_html/subdomains/new/uploads/user/{id}/player_photos/...`

## Příklady
- Všechno (všechny povolené týmy a sezóny), šetrnější tempo:
```bash
php artisan app:sync-player-photos --delay=3 --per-player-delay-ms=300 -n
```
- Jen konkrétní tým a tato sezóna, přepsat vše:
```bash
php artisan app:sync-player-photos --team_id=7761 --season_id=2025 --force -n
```
- Rychlý test lokálně na jedné konfiguraci:
```bash
php artisan app:sync-player-photos --batch-size=1 -n
```

## Poznámky
- UI (např. "Nejlepší hráči" v detailu zápasu) preferuje lokální fotky z `player_photos`, pokud jsou dostupné.
- Pokud se změnily CSS/JS assety, nezapomeňte na `npm run build` (manifest Vite).
