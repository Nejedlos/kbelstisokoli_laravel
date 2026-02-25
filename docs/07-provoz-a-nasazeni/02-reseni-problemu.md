# Troubleshooting - Nasazení na produkci

Tento dokument slouží jako deník vyřešených problémů při nasazování aplikace Kbelští sokoli na hosting Webglobe.

## 1. Chyba: Access denied for user (using password: NO/YES)
**Příznak:** Během `php artisan app:sync` nebo `app:deploy` selže migrace databáze s chybou `SQLSTATE[HY000] [1045] Access denied for user...`.

**Příčiny a řešení:**
1. **Prázdné heslo v .env:** Envoy script na serveru přepsal heslo prázdnou hodnotou, protože v lokálním `.env` chyběl klíč `PROD_DB_PASSWORD`.
   - *Řešení:* Vždy vyplňte `PROD_DB_PASSWORD` v lokálním `.env` nebo použijte interaktivní dotaz při startu příkazu.
2. **Neplatné heslo v .env:** V lokálním `.env` bylo uloženo zastaralé nebo náhodně vygenerované heslo, které neodpovídalo realitě na serveru.
   - *Řešení:* Ověřte heslo v administraci hostingu. Pozor na zapomenuté soubory typu `public/.env`, které mohou obsahovat citlivé (a správné) údaje z předchozích pokusů. **Tyto soubory ihned po zjištění smažte!**
3. **Chyba v uvozovkách/speciálních znacích:** Pokud heslo obsahuje znaky jako `$`, `#`, `"` nebo `/`, mohlo dojít k chybě při zápisu do `.env` na serveru.
   - *Řešení:* Používejte aktualizovanou verzi `Envoy.blade.php`, která pro aktualizaci `.env` využívá robustní PHP skript s base64 kódováním a správným escapováním.

## 2. Chyba: SQL syntax error (JSON column type)
**Příznak:** Migrace selže s chybou `SQLSTATE[42000]: Syntax error or access violation: 1064 ... for column ... json`.

**Příčina:** Hosting Webglobe využívá MariaDB 10.6, která sice JSON formát podporuje, ale Laravel 12 se pokouší použít nativní typ `JSON`, který tento systém v dané verzi nezná pro operace přidávání sloupců.

**Řešení:**
- V migracích nepoužívejte `$table->json('column')`.
- Místo toho použijte `$table->text('column')` nebo `$table->longText('column')`.
- V modelu Laravelu ponechte `$casts = ['column' => 'array']`. Laravel se o převod JSON -> Array postará automaticky i nad textovým sloupcem.

## 3. Chyba: Zablokování v neinteraktivním prostředí (CI/CD / AI)
**Příznak:** Příkaz `php artisan app:sync` se "zasekne", protože čeká na odpověď (heslo), kterou v automatizovaném prostředí nikdo nezadá.

**Řešení:**
- Použijte přepínač `--ai-test`: `php artisan app:sync --ai-test`.
- Tento přepínač vynutí použití uložených hodnot z `.env` a přeskočí všechny interaktivní dotazy.

## 4. Chyba: Too many levels of symbolic links
**Příznak:** Při pokusu o spuštění `node` nebo `npm` na serveru dojde k této chybě.

**Příčina:** Symlinky v `.node_bin` se zacyklily (např. `node` ukazuje na `node`).

**Řešení:**
- Smažte složku `.node_bin` na serveru: `rm -rf .node_bin`.
- Znovu spusťte `php artisan app:production:setup` pro správné přegenerování symlinků na absolutní cesty binárek.
