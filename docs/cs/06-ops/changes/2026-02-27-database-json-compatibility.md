# Kompatibilita databáze (JSON vs LongText)

## Popis změny
Produkční prostředí (hosting Webglobe) nepodporuje nativní datový typ `json` v databázi MariaDB/MySQL. Aby byla zajištěna kompatibilita a funkčnost translatable polí i metadat, byla všechna pole typu `json` v migracích nahrazena typem `longText`.

Laravel a balíček `spatie/laravel-translatable` automaticky zvládají serializaci a deserializaci JSON dat do textového pole, takže funkčnost kódu zůstává zachována.

## Provedené úpravy
1. **Dokumentace:**
   - Aktualizován soubor `docs/01-zakladni-koncepty/05-lokalizace.md` – nyní uvádí `longText` jako povinný typ pro překlady.
   - Aktualizován soubor `docs/02-vyvoj-a-standardy/07-idempotentni-migrace.md` – přidána sekce o omezení typu `json` v produkci.

2. **Migrace:**
   - Opraveny všechny existující migrace, které používaly `$table->json()`, na `$table->longText()`:
     - `database/migrations/2026_02_21_161118_create_club_competitions_tables.php`
     - `database/migrations/2026_02_21_155625_create_attendances_table.php`
   - Nové migrace (např. `fine_templates`) již tento standard dodržují.

## Doporučení pro vývoj
Při vytváření nových migrací **nikdy** nepoužívejte `$table->json()`. Vždy volte `$table->longText()`, pokud pole vyžaduje ukládání strukturovaných dat (překlady, metadata).
