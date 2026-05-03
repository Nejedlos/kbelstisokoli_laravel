# Lokální vývoj a optimalizace výkonu

Tento dokument obsahuje doporučení a technické detaily k optimalizaci výkonu projektu na lokálním prostředí (localhost).

## 1. Optimalizace provedené v březnu 2026

Aby se předešlo zpomalení administrace při lokálním vývoji (zejména vlivem pollingu a SPA navigace), byly provedeny následující změny:

### A. Omezení Laravel Telescope (TelescopeServiceProvider)
- **Model Hydrations:** Vypnuto na localhostu. Zaznamenávání každé hydratace modelu při velkých seznamech (např. v administraci) generuje stovky zápisů do DB.
- **Slow Queries:** SQL dotazy se na localhostu zaznamenávají pouze tehdy, pokud trvají déle než **50ms**.
- **ViewWatcher:** Vypnuto na localhostu (velký režijní náklad na paměť).
- **Filtrování Livewire:** Požadavky na `/livewire/update`, které nejsou chybové, se do Telescope neukládají, aby se předešlo "zahlcení" DB pollingem.

### B. Optimalizace Vite (vite.config.js)
- Watcher nyní ignoruje:
    - Všechny `.html` soubory v rootu (často statické exporty nebo pomocné soubory).
    - `.env` soubory, `composer.lock`, `package-lock.json`.
    - `storage/logs` a `storage/framework/views`.
- Toto výrazně snižuje zatížení CPU při zapnutém `npm run dev` na MacOS a Windows.

### C. Rychlejší boot nastavení (PerformanceService)
- Nastavení výkonu (`PerformanceService::getSettings()`) nyní na localhostu vynucuje ovladač **'file'**, i když je globální výchozí ovladač nastaven na **'database'**.
- Tím se eliminuje jeden DB dotaz při každém bootu aplikace (každý request), což je kritické u SQLite.

## 2. Doporučení pro vývojáře

### A. Nepoužívejte `php artisan serve` pro běžnou práci
Vestavěný PHP server je **jednovláknový**. Livewire polling (např. Sync Bar) může "zablokovat" vlákno a další požadavky (např. kliknutí na menu) musí čekat, dokud polling nedoběhne.
- **Doporučení:** Používejte **Laravel Herd** (MacOS/Windows) nebo **Laravel Valet** (MacOS). Tyto nástroje používají Nginx/FPM a zvládnou více souběžných požadavků.

### B. Vypněte Xdebug, pokud jej zrovna nepotřebujete
Xdebug (zejména s povoleným `step_debugger`) zpomaluje PHP o 200–500 %. Pro běžné procházení administrace je vhodné jej mít vypnutý.

### C. Cache ovladač
Pokud nepoužíváte Redis, Laravel Herd/Valet standardně používají `file` pro cache a sessions. Pokud máte v `.env` nastaveno `CACHE_STORE=database`, zvažte přepnutí na `file` pro lokální vývoj, pokud nepotřebujete testovat specifické chování DB cache.

## 3. Databázové indexy
## 4. Striktní oddělení prostředí (Local vs. Production)

V tomto projektu je kriticky důležité striktně oddělovat konfiguraci pro lokální vývoj a produkci.

### A. Lokální prostředí (Herd)
- **.env:** Obsahuje nastavení pro lokální vývoj na Laravel Herd.
- **SMTP:** Používá Mailpit nebo Mailtrap na `127.0.0.1` port `1025`.
- **APP_URL:** Obvykle `http://kbelstisokoli.test`.
- **ZÁKAZ:** Nikdy do lokálního `.env` nezapisujte reálné produkční SMTP údaje do proměnných `MAIL_*`. Pokud je potřebujete uložit pro účely nasazení, používejte prefix `PROD_MAIL_*`.

### B. Produkční prostředí (Webglobe)
- **.env na serveru:** Spravováno přes SSH a automatizované nasazovací skripty.
- **SMTP:** Reálné údaje Webglobe (`mail.webglobe.cz`, port `465`, SSL).
- **Správa:** Změny v produkčním `.env` provádějte buď ručně přes SSH, nebo pomocí příkazů `php artisan app:production:setup` a `php artisan app:deploy`, které se postarají o bezpečný přenos hodnot ze souboru `.env` (hledají prefixy `PROD_*`) na server.
- **.env.production:** Slouží pouze jako šablona a dokumentace produkčních hodnot. Skutečná pravda je v `.env` souboru na serveru.
