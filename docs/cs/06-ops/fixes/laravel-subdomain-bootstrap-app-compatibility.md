# Fix: Kompatibilita bootstrap/app.php a verze frameworku (usePublicPath)

## Problém
Po nasazení aplikace na subdoménu s využitím vlastního `index.php` docházelo k fatální chybě:
`Fatal error: Uncaught Error: Call to undefined method Illuminate\Foundation\Configuration\ApplicationBuilder::usePublicPath() in .../bootstrap/app.php`

## Příčina (Root Cause)
V souboru `bootstrap/app.php` byla metoda `usePublicPath()` volána v rámci řetězeného volání na objektu `ApplicationBuilder` (který vrací `Application::configure()`). Ačkoliv novější verze Laravelu 11/12 tuto metodu do builderu přidaly, aktuálně nainstalovaná verze frameworku ji v tomto builderu postrádá, což vedlo k pádu aplikace. Metoda je však stabilně dostupná přímo na instanci `Application`.

## Provedená oprava
Soubor `bootstrap/app.php` byl upraven tak, aby se volání `usePublicPath()` provedlo až na výsledné instanci aplikace po zavolání `->create()`.

**Původní kód:**
```php
return Application::configure(basePath: dirname(__DIR__))
    ->usePublicPath(...)
    ->withRouting(...)
    // ...
    ->create();
```

**Nový kód:**
```php
$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(...)
    // ...
    ->create();

$app->usePublicPath(realpath(env('APP_PUBLIC_PATH', base_path('public'))));

return $app;
```

## Jak ověřit
1. Spustit libovolný Artisan příkaz (např. `php artisan --version`). Pokud neproběhne pád s `Fatal error`, oprava je funkční.
2. Webové rozhraní přes subdoménu by se mělo začít načítat (pokud neexistují jiné chyby v konfiguraci serveru).

## Doporučené následné kroky (Post-deploy)
Po nahrání změny na server je doporučeno provést:
1. `php artisan optimize:clear` (pokud artisan běží).
2. Pokud artisan neběží kvůli cache, ručně smazat soubory v `bootstrap/cache/*.php`.
3. Zkontrolovat `.env` soubor, zda `APP_URL` odpovídá produkční adrese a zda je případně vyplněna `APP_PUBLIC_PATH`, pokud se veřejná složka liší od standardní `/public`.

## Rollback postup
Vrátit změny v souboru `bootstrap/app.php` do předchozího stavu pomocí Git:
```bash
git checkout bootstrap/app.php
```
