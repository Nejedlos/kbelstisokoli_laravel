# Optimalizace výkonu (Performance Optimization)

Tento dokument popisuje provedená opatření pro zrychlení aplikace Kbelští sokoli a postupy pro udržení vysokého výkonu.

## Provedené změny (Březen 2026)

### 1. Middleware Cleanup
- **Odstranění Log::info:** Z middleware `EnsureTwoFactorEnabled` a `CheckTwoFactorTimeout` byla odstraněna nadměrná logování, která probíhala při každém requestu.
- **Ignorování statických souborů v 404 loggeru:** `NotFoundLoggerMiddleware` nyní ignoruje přípony `.png, .jpg, .jpeg, .gif, .svg, .webp, .ico, .css, .js, .map`, což šetří zápisy do databáze při chybějících assetech.

### 2. BrandingService Optimization
- **Efektivní query:** `getDbSettings()` nyní načítá pouze klíče, které BrandingService reálně potřebuje (místo `Setting::all()`).
- **Schema Cache:** Výsledek `Schema::hasTable('settings')` je nyní cachován (`schema_has_settings_table`), aby se předešlo drahým DB dotazům na strukturu tabulek v každém požadavku.
- **Request-level cache:** Data jsou držena v paměti po celou dobu trvání požadavku.

### 3. Assety a Externí závislosti
- **Lokální Cropper.js:** Knihovna Cropper.js byla stažena lokálně do `public/assets/vendor/`, čímž se eliminovala závislost na externím CDN v administraci.
- **Preconnect pro Fonty:** Přidány `<link rel="preconnect">` pro Google Fonts v `AdminPanelProvider` i v auth layoutu, což urychluje navázání spojení s font servery.
- **FOUC stabilizace:** Vylepšena ochrana proti problikávání velkých ikon (Font Awesome) před načtením CSS.

### 4. Konfigurace a Caching
- **Ultra Scénář:** V `config/performance.php` byl nastaven výchozí scénář na `ultra`.
- **SPA feel:** Aktivováno `wire:navigate` pro plynulé přechody v administraci (v rámci ultra scénáře).
- **Laravel Cache:** Aktivovány všechny standardní Laravel cache (`config`, `route`, `view`, `filament components`).

## Nástroje pro diagnostiku

Pro průběžné ověřování výkonu byl vytvořen Artisan příkaz:

```bash
php artisan app:perf
```

Tento příkaz měří latenci databáze, souborového systému a kontroluje stav všech cache mechanismů.

## Postup při nasazení (Deployment Checklist)

Při každém nasazení na produkci je nutné zajistit:
1. `php artisan config:cache`
2. `php artisan route:cache`
3. `php artisan view:cache`
4. `php artisan filament:cache-components`
5. `npm run build` (pro aktualizaci manifestu)

## Doporučení pro vývoj
- **Nepřidávat Log::info do globálních middlewarů.**
- **Vždy používat eager loading (with()) v Eloquentu.**
- **Při přidání nového nastavení do BrandingService aktualizovat seznam klíčů v `getDbSettings()`.**
