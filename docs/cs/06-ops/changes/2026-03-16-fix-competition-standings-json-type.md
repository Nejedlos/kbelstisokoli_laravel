# Oprava datového typu pro tabulku competition_standings

## Popis problému
Při nasazení nové tabulky `competition_standings` došlo k chybě na produkčním serveru (Webglobe), protože migrace používala nativní datový typ `json` pro sloupec `metadata`. 

Hosting Webglobe používá verzi MariaDB/MySQL, která tento typ v definici tabulky nepodporuje přes standardní Laravel metodu `$table->json()`.

## Provedené změny
1. **Migrace:**
   - V souboru `database/migrations/2026_03_16_181015_create_competition_standings_table.php` byl změněn typ sloupce `metadata` z `json()` na `longText()`.
   - Toto je v souladu s ostatními tabulkami v projektu, které ukládají JSON data (např. translatable pole).

2. **Model:**
   - Ověřeno, že model `App\Models\CompetitionStanding` má správně nastaven `$casts = ['metadata' => 'array']`, což zajišťuje automatickou serializaci i v `longText` sloupci.

## Doporučení
Při vytváření nových migrací se striktně držte pravidla popsaného v `docs/cs/02-development/15-json-sloupce-a-kompatibilita.md` a **nepoužívejte** typ `json()`. Vždy používejte `longText()`.
