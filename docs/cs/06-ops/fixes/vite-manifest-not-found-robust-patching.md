# Fix: Robustní patchování index.php pro subdomény (Vite Manifest Not Found)

## Problém
Aplikace nasazená na subdoméně (např. `new.kbelstisokoli.cz`) hlásila chybu `ViteManifestNotFoundException`. Příčinou bylo, že Laravel nesprávně detekoval "public path" a hledal Vite manifest v interní složce projektu (`/secret/public/build/`) místo ve skutečné veřejné složce subdomény.

Předchozí pokusy o automatické patchování `index.php` selhávaly, pokud byl soubor již částečně upraven nebo pokud regexy neodpovídaly přesně formátu souboru.

## Root Cause
1. **Idempotence:** Původní regexy v `Envoy.blade.php` hledaly specificky řetězec `__DIR__ . '/..`. Pokud už byl soubor jednou napatchován na absolutní cestu, regexy jej podruhé nenašly a nedoplnily chybějící volání `$app->usePublicPath(__DIR__);`.
2. **Escapování v Envoy:** Docházelo k problémům s interpretací znaků `$` shellem při spouštění PHP příkazů v rámci Envoy úloh.

## Provedená oprava
1. **Aktualizace `Envoy.blade.php`:**
    - PHP skript pro patchování `index.php` byl přepracován tak, aby používal robustní regulární výrazy.
    - Regexy nyní identifikují `autoload.php`, `bootstrap/app.php` a `maintenance.php` bez ohledu na to, zda používají relativní (`__DIR__`) nebo absolutní cestu.
    - Skript nyní před patchováním proaktivně čistí předchozí volání `usePublicPath`, aby nedocházelo k jejich duplikaci.
    - Bylo opraveno escapování (použití single quotes v shellu), což zaručuje, že se do `index.php` zapíše korektní PHP kód.
2. **Zajištění Public Path:**
    - Do `index.php` se nyní spolehlivě vkládá řádek:
      `$app->usePublicPath(__DIR__);`
    - To vynutí, aby Laravel (a Vite) hledali assety a manifest přímo v adresáři, kde se nachází spuštěný `index.php`.

## Jak ověřit
1. Spusťte nasazení: `php artisan app:deploy`
2. Po dokončení zkontrolujte obsah `index.php` v adresáři subdomény na serveru. Měl by obsahovat:
   ```php
   $app = require_once '/cesta/k/projektu/bootstrap/app.php';
   $app->usePublicPath(__DIR__);
   ```
3. Web by měl nyní správně načítat CSS a JS přes Vite bez chyb.

## Rollback
V případě potíží lze v `index.php` na subdoméně ručně opravit cesty nebo smazat řádek s `usePublicPath`. Skript v `Envoy.blade.php` lze vrátit pomocí `git checkout Envoy.blade.php`.
