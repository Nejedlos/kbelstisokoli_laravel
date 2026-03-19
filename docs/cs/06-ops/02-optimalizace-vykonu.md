# Optimalizace výkonu na produkci

Tento dokument shrnuje provedené optimalizace výkonu pro projekt Kbelští sokoli na hostingu Webglobe.

## 1. PHP a OPcache
- **Verze PHP:** 8.4
- **Optimalizace:** Vytvořen soubor `.user.ini` v `public_html/` s následujícím nastavením:
    - `opcache.validate_timestamps=0` (vypne kontrolu změn souborů při každém requestu - VYŽADUJE restart PHP/vymazání cache při deployi).
    - `opcache.memory_consumption=256` (zvýšena paměť pro cache).
    - `opcache.interned_strings_buffer=16`.

## 2. Cache a Session (Redis)
- Aplikace byla přepnuta z `file` (pomalý NFS/disk) na **Redis**.
- **Konfigurace v .env:**
    - `CACHE_STORE=redis`
    - `SESSION_DRIVER=redis`
    - `REDIS_HOST=127.0.0.1`
- **Výhoda:** Extrémně rychlá odezva pro session a settings cache, eliminace zámků souborového systému.

## 3. Laravel Optimalizace
- Spuštěn příkaz `php artisan optimize`, který:
    - Nacachuje konfiguraci (`config:cache`).
    - Nacachuje routy (`route:cache`).
    - Skompiluje Blade views.
- **Důležité:** Při každém ručním zásahu do `.env` nebo kódu je nutné spustit `php artisan optimize:clear` a znovu `php artisan optimize`.

## 4. Kódové optimalizace
### Middleware
- **PerformanceProfilingMiddleware:** Upraven tak, aby na produkci nezapínal `DB::enableQueryLog()`, pokud není aktivní speciální profilovací mód. To ušetřilo cca 100-200ms z bootstrapu.
- **InjectFeedbackWidget:** Přidána rychlá kontrola přípon souborů. Pokud request směřuje na obrázek nebo CSS, middleware okamžitě končí a neanalyzuje HTML obsah.

### Služby
- **PerformanceService:** Nyní prioritně využívá Redis pro cachování nastavení aplikace, čímž eliminuje DB dotazy v každém requestu.

## 5. Fronty (Queues)
- Protože Webglobe nepodporuje `pcntl` (nutné pro Horizon), byl spuštěn proces `php artisan queue:listen --timeout=0` na pozadí.
- Zpracování náročných úloh (AI, maily) probíhá asynchronně.

## 6. Doporučení pro vývojáře
- Pokud se změny v kódu neprojevují, je to pravděpodobně kvůli `opcache.validate_timestamps=0`.
- **Řešení:** V administraci v SystemConsole nebo přes SSH spusťte `php artisan optimize:clear` (toto by mělo vyčistit i OPcache pokud je správně nakonfigurován fcgi protokol, jinak je nutné vyčkat na automatický timeout nebo restart PHP procesu v administraci hostingu).
