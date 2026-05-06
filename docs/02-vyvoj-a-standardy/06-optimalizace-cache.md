# Optimalizace Full Page Cache

Tento dokument popisuje změnu v mechanismu cachování frontendu, která byla provedena na základě požadavku na pravidelné přegenerovávání obsahu.

## Původní stav
- Full Page Cache byla pasivní: pokud existovala, použila se. Pokud ne, vygenerovala se při prvním požadavku hosta.
- Cache se invalidovala buď expirací (TTL), nebo selektivně při změně dat (u podporovaných driverů jako Redis/Database).
- Na sdíleném hostingu (Webglobe) s `file` driverem byla selektivní invalidace omezená.

## Nový stav
1.  **Pravidelný Priming:** Cache se nyní aktivně přegenerovává každých **30 minut** pomocí cronu (`page-cache:prime`). Proces automaticky prochází obě jazykové verze (**CS** i **EN**).
2.  **In-place Update:** Middleware `FullPageCacheMiddleware` byl upraven tak, aby při požadavku s hlavičkou `X-Prime-Cache` (kterou posílá priming command) ignoroval stávající cache a uložil novou verzi. Tím dochází k aktualizaci obsahu bez nutnosti explicitního mazání souborů.
3.  **Výchozí aktivace:** V `config/performance.php` bylo nastaveno výchozí zapnutí Full Page Cache a Fragment Cache na `true`.
4.  **Bezpečnostní omezení (Prevence CSRF/Auth chyb):** Middleware byl zpřísněn, aby nedocházelo k cachování citlivých stránek nebo stránek s formuláři.
    - Vyloučení všech auth-related cest (`login`, `logout`, `password*`, `two-factor*`, atd.).
    - Zákaz cachování, pokud odpověď obsahuje CSRF token (`name="_token"`).
    - Zákaz cachování, pokud session obsahuje flash data (např. chyby validace).
    - Zákaz cachování pro přihlášené uživatele (stále platí).

## Technické detaily
- **Cron úloha:** Definována v `routes/console.php`.
- **Bypass mechanizmus:** V `FullPageCacheMiddleware` přidána podmínka: `if (Cache::has($cacheKey) && ! $request->hasHeader('X-Prime-Cache'))`.
- **Výhoda:** Uživatelé (hosté) vždy dostanou předgenerovanou verzi stránky, která je maximálně 30 minut stará, což výrazně zvyšuje rychlost načítání (TTFB) i na levnějším hostingu.

## Doporučení pro produkci
Pro správnou funkci se ujistěte, že v produkčním `.env` souboru **není** nastaveno `PERF_FULL_PAGE_CACHE=false`. Pokud tam tato proměnná je, měla by mít hodnotu `true`.
