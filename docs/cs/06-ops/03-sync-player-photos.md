# Sync hráčských fotek (jen fotky)

Tento nástroj stáhne a uloží fotografie hráčů bez synchronizace statistik. Umí projít všechny povolené týmy a jejich sezóny z `ExternalTeamSeasonConfig`, případně lze omezit pouze na vybraný tým/sezónu. Respektuje rate‑limit a podporuje přepsání existujících fotek.

## Příkaz

```bash
php artisan app:sync-player-photos [--team_id=] [--season_id=] [--force] [--delay=] [--per-player-delay-ms=] [--batch-size=] [--matches] -n
```

### Parametry
- `--team_id=` – interní ID týmu v aplikaci; pokud není zadáno, projdou se všechny povolené týmy.
- `--season_id=` – interní ID sezóny; pokud není zadáno, projdou se všechny povolené sezóny týmu.
- `--force` – stáhne a přepíše fotky i tam, kde už byla tato konkrétní `source_url` uložena.
- `--delay=` – pauza mezi konfiguracemi tým×sezóna v sekundách (default 1s).
- `--per-player-delay-ms=` – pauza mezi hráči v milisekundách (default 200ms).
- `--batch-size=` – zpracuje pouze prvních N konfigurací (užitečné pro testování).
- `--matches` – projde i sekce "Nejlepší hráči" ve všech odehraných zápasech daného týmu a sezóny (stáhne fotky i pro soupeře).

## Chování
- Základem je soupisková stránka `team_season_url`. Pokud řádek soupisky obsahuje obrázek, stáhne se přímo tento zdroj.
- Pokud je aktivní příznak `--matches`, příkaz projde všechny zápasy v databázi pro daný tým a sezónu, vytáhne z jejich metadat nejlepší hráče (včetně soupeřů), vytvoří jim "ghost" profily a stáhne jejich fotografie.
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
