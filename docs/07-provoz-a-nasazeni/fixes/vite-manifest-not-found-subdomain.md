### Diagnostika a oprava "Vite manifest not found" na subdoméně

#### Problém
Aplikace na produkčním serveru (subdoména `new.kbelstisokoli.cz`) padala s chybou `Illuminate\Foundation\ViteManifestNotFoundException`. Vite hledal `manifest.json` v interní složce projektu (`.../secret/public/build/`) místo ve veřejné složce subdomény (`.../subdomains/new/build/`).

Hlavní příčinou bylo nesprávné patchování souboru `index.php` v deployment skriptu. V Laravelu 12 má `index.php` jinou strukturu než v předchozích verzích, a původní regulární výraz pro vložení `$app->usePublicPath(__DIR__)` selhal, protože hledal přiřazení do proměnné `$app`, které je v novém `index.php` volitelné (často je tam jen `(require ...)->handleRequest(...)`).

#### Provedená řešení
1.  **Úprava `Envoy.blade.php`**:
    - Aktualizovány regulární výrazy pro patchování `index.php` tak, aby byly kompatibilní s Laravel 11/12 i staršími styly. Nyní správně detekují a upravují cestu k `bootstrap/app.php` a vkládají volání `usePublicPath(__DIR__)` bez ohledu na to, zda je výsledek require přiřazen do proměnné.
    - Úprava byla aplikována v úlohách `setup`, `deploy` i `sync`.
2.  **Úprava `bootstrap/app.php`**:
    - Změněna logika nastavení veřejné cesty. Pokud není v `.env` definována proměnná `APP_PUBLIC_PATH`, Laravel si ponechá svou výchozí cestu (kterou si pak případně přepíše `index.php`). Tím se předešlo potenciálním konfliktům a problémům s `realpath()`, který mohl vracet `false` pro neexistující cesty.
3.  **Verifikace**:
    - Oprava zajišťuje, že webové požadavky přes subdoménu vždy nastaví `public_path()` na adresář, kde se nachází `index.php`, což je pro Vite klíčové pro nalezení manifestu.

#### Změněné soubory
- `Envoy.blade.php`: Robustnější patchování `index.php`.
- `bootstrap/app.php`: Bezpečnější inicializace veřejné cesty.

#### Doporučený postup
Po nasazení těchto změn (např. pomocí `php artisan app:deploy`) dojde k přepsání `index.php` na subdoméně správným obsahem. Pokud by problém přetrvával, zkontrolujte, zda soubor `index.php` v `/home/html/kbelstisokoli.cz/public_html/subdomains/new/` obsahuje řádek:
```php
$app->usePublicPath(__DIR__);
```
před voláním `$app->handleRequest(...)`.
