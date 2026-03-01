# Optimalizace výkonu (Březen 2026)

Tento dokument shrnuje provedené změny pro zrychlení projektu ve všech jeho částech (Frontend, Member, Admin).

## 1. Optimalizace View Composers (Kritické)
- **Změna:** V `AppServiceProvider` byla přidána statická cache pro `unreadNotificationsCount` a `cachedData`.
- **Důvod:** Dotaz na počet notifikací a načítání brandingu se prováděl pro KAŽDÉ vykreslené view (i sub-views v layouts, public, member), což generovalo desítky SQL dotazů na jeden request.
- **Výsledek:** Počet dotazů na notifikace klesl na 1 na request.

## 2. Oprava N+1 dotazů v Administraci (Filament)
- **BasketballMatchResource:** Přidán eager loading pro `teams`, `opponent`, `season` a `withCount` pro `mismatches`.
- **ClubEventResource:** Přidán eager loading pro `teams`.
- **PlayersRelationManager (v Týmech):** Přidán eager loading pro relaci `user`.
- **Výsledek:** Výrazné zrychlení načítání tabulek v administraci, zejména u soupisek a zápasů.

## 3. Optimalizace Dashboardu člena
- **DashboardController:** Přidán `loadMissing(['playerProfile.teams'])` uvnitř cachované closure.
- **Výsledek:** Snížení počtu dotazů při načítání dat pro dashboard uživatele.

## 4. Oprava invalidace fragmentů (Frontend)
- **Změna:** Přidán prefix `fragment_` do klíčů `@cacheFragment` v `page-blocks.blade.php` a `news_listing.blade.php`.
- **Důvod:** `PerformanceObserver` neinvalidoval tyto klíče, protože neodpovídaly masce `fragment_%`. To způsobovalo, že se dynamický obsah neaktualizoval ihned po uložení modelu.
- **Výsledek:** Konzistentnější a aktuálnější obsah na frontendu bez nutnosti ručního promazávání cache.

## 5. Údržba systému
- Proveden `php artisan optimize:clear`.
- Vyčištění a přegenerování cache konfigurace, cest a view.

---
*Provedeno Junie dne 1. 3. 2026*
