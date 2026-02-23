# Seedování dat (Databázové seedy)

Tento dokument popisuje systém seedování dat v projektu Kbelští sokoli, který zajišťuje konzistentní stav aplikace (výchozí nastavení, uživatelé, role, CMS obsah) napříč vývojovým a produkčním prostředím.

## 1. Architektura seederů

Projekt využívá hierarchický systém seederů s hlavním bodem v `GlobalSeeder.php`.

### Hlavní seedery
- **`GlobalSeeder`**: Centrální seeder, který definuje seznam ostatních seederů (`SEEDERS`) a tabulek pro promazání (`TABLES_TO_WIPE`).
- **`RoleSeeder`**: Definice rolí (`admin`, `editor`, `member`, `guest`) a jejich oprávnění.
- **`UserSeeder`**: Vytváří výchozí uživatele (administrátora a testovacího editora).
- **`CronTaskSeeder`**: Registruje systémové úlohy pro plánovač (RSVP upomínky, synchronizace statistik atd.).
- **`SportSeeder`**: Základní data pro sportovní část (sezóny, týmy jako Muži C a Muži E).
- **`PostSeeder`**: Kategorie novinek a úvodní uvítací článek.
- **`CmsContentSeeder`**: Kompletní struktura webu – nastavení (Settings), stránky (Pages), bloky (PageBlocks), menu a SEO metadata.

## 2. Příkaz `app:seed`

Pro usnadnění práce byl vytvořen speciální příkaz `php artisan app:seed`, který obaluje standardní `db:seed` a přidává užitečné funkce.

### Použití
- `php artisan app:seed` – Spustí všechny seedery v pořadí definovaném v `GlobalSeeder`.
- `php artisan app:seed --fresh` – Před spuštěním seederů **smaže všechna data** v tabulkách definovaných v `GlobalSeeder::TABLES_TO_WIPE`.
- `php artisan app:seed --force` – Vynutí spuštění na produkci (předává se do vnitřního `db:seed`).
- `php artisan app:seed --class=UserSeeder` – Spustí pouze konkrétní seeder.

### Idempotence
Většina seederů používá metodu `updateOrCreate` nebo kontroluje existenci záznamů, takže je lze bezpečně spouštět opakovaně bez duplikace dat.

## 3. Seedování na produkci

Na produkčním prostředí je nutné dbát zvýšené opatrnosti, zejména při použití příznaku `--fresh`.

### Jak spustit hlavní seeder na produkci
Pro spuštění seederů na produkci použijte příkaz:

```bash
php artisan app:seed --force --no-interaction
```

Pokud byste z nějakého důvodu chtěli použít standardní Laravel příkaz (např. pokud custom command selže):

```bash
php artisan db:seed --class=GlobalSeeder --force
```

### Fresh seed na produkci (VAROVÁNÍ)
Pokud potřebujete uvést produkční databázi do čistého výchozího stavu (smaže to uživatele, nastavení i CMS obsah!):

```bash
php artisan app:seed --fresh --no-interaction
```

*Poznámka: Na produkci se příkaz `app:seed` zeptá na potvrzení, pokud nepoužijete `--no-interaction`.*

## 4. Přidání nového seederu
1. Vytvořte seeder: `php artisan make:seeder MujNovySeeder`
2. Implementujte logiku (ideálně pomocí `updateOrCreate`).
3. Přidejte třídu do `GlobalSeeder::SEEDERS`.
4. Pokud seeder naplňuje tabulku, která by se měla při fresh startu čistit, přidejte název tabulky do `GlobalSeeder::TABLES_TO_WIPE`.
