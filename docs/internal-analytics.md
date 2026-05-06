# Interní analytika provozu

Tento modul slouží k internímu sledování provozu aplikace, návštěvnosti jednotlivých sekcí, přihlašování uživatelů a sledování technických parametrů requestů (chyby, rychlost).

## Co modul dělá
- Sleduje každý request (kromě ignorovaných cest a assetů) a zaznamenává jej do tabulky `internal_analytics_events`.
- Rozlišuje sekce: Frontend, Členská sekce, Administrace, API.
- Sleduje přihlášení, odhlášení a neúspěšné pokusy o přihlášení.
- Detekuje pomalé requesty (výchozí práh 1000ms).
- Detekuje chybové requesty (status code 4xx a 5xx).
- Poskytuje dashboard v administraci pro superadminy.

## Ochrana soukromí (GDPR)
- IP adresy se neukládají v čisté podobě, ale jsou hashovány pomocí `APP_KEY`.
- Session ID jsou hashovány.
- Neukládají se těla requestů (request body) ani query parametry v plné podobě.
- Identifikace návštěvníka probíhá přes kombinaci hashované IP a User-Agentu.

## Konfigurace
Konfigurace se nachází v `config/internal-analytics.php`.

Klíčové volby:
- `enabled`: Globální vypínač modulu.
- `retention_days`: Počet dní pro uchování surových dat (výchozí 90).
- `slow_request_threshold_ms`: Práh pro označení requestu za pomalý.
- `ignored_paths`: Seznam cest, které se nesledují.

## Dashboard
Dashboard je přístupný v administraci v sekci **Systém -> Analytika provozu**. Přístup mají pouze uživatelé s rolí `admin`.

## Příkazy (CLI)
- `php artisan internal-analytics:cleanup`: Smaže data starší než definovaný retention period.
- `php artisan internal-analytics:aggregate`: Vytvoří denní souhrny (vhodné pro dlouhodobé statistiky).

## Plánování (Scheduler)
Doporučuje se přidat příkazy do plánovače:

```php
$schedule->command('internal-analytics:cleanup')->daily();
$schedule->command('internal-analytics:aggregate')->dailyAt('00:05');
```

## Rollback
V případě potřeby lze modul odstranit následovně:
1. Odstranit middleware z `bootstrap/app.php`.
2. Odstranit listener z `EventServiceProvider.php`.
3. Smazat soubory modulu.
4. Spustit rollback migrací (pokud je to žádoucí).
