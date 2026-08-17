# Oprava pádů RedirectMiddleware při výpadku DB (2026-08-17)

Tento dokument zaznamenává stabilizaci `RedirectMiddleware`, který způsoboval pád celého webu v případě nedostupnosti databáze.

## 1. Popis chyby (QueryException)
- **Chyba:** `SQLSTATE[HY000] [2002] Connection refused`
- **Příčina:** Middleware se při každém požadavku snaží načíst pravidla přesměrování z databáze (přes `Cache::remember`). Pokud je databáze nedostupná (výpadek MySQL serveru na hostingu), vyhodí Laravel `QueryException`.
- **Dopad:** Protože middleware běží globálně, znefunkčnil web i pro požadavky, které by jinak mohly být vyřízeny z mezipaměti (např. Redis), nebo by mohly zobrazit uživatelsky přívětivější chybovou stránku.

## 2. Implementované řešení
- **Ošetření výjimek:** Všechny operace s databází a mezipamětí v `RedirectMiddleware.php` byly obaleny do `try-catch` bloků zachytávajících `\Throwable`.
- **Logika zotavení:**
    - Pokud dojde k chybě připojení při načítání pravidel, middleware chybu zapíše do logu jako `warning` a tiše pokračuje v dalším zpracování požadavku (`$next($request)`).
    - Pokud dojde k chybě při zápisu statistik (inkrementace `hits_count`), chyba se zapíše do logu jako `error`, ale přesměrování se přesto provede.
- **Odolnost:** Web nyní "přežije" výpadek DB v tomto kroku. Pokud je cílová stránka plně nacachovaná (např. v Redis přes `FullPageCacheMiddleware`), bude fungovat i při úplném výpadku MySQL.

## 3. Verifikace
- **Statická analýza:** Kód byl revidován a splňuje standardy projektu (anglické názvosloví, české logování).
- **Logování:** Chyby jsou korektně zaznamenávány do Laravel logu, což umožňuje diagnostiku problémů s připojením bez totálního výpadku frontendových služeb.
