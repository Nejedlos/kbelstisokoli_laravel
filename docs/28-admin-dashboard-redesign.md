### Redesign administrace – Dashboard

Tento dokument shrnuje provedené úpravy hlavní nástěnky administrace (Filament), které reflektují požadavek na moderní, přehledné a plně lokalizované „řídící centrum“ s důrazem na stav oddílu a stav systému.

#### Cíle
- Odstranit nerelevantní widgety (uživatelský/odhlašovací panel a Filament Info) z hlavní stránky.
- Zajistit plnou bilingvní lokalizaci (cs/en) všech popisků a statistik na dashboardu.
- Přidat rychlé akce a přehledy: uvítací banner, KPI klubu, stav systému a poslední aktivity.

#### Implementované widgety
- WelcomeBannerWidget
  - Umístění: `app/Filament/Widgets/WelcomeBannerWidget.php`
  - Šablona: `resources/views/filament/widgets/welcome-banner-widget.blade.php`
  - Funkce: uvítací hlavička s počtem aktivních hráčů a rychlými akcemi pod sebou (s "hinty").
    - Akce: Nový zápas, Nový člen, Napsat novinku, Nový trénink, Nová akce, Multimédia, Auditní log, Finance.
    - Responzivita: na mobilu 1 sloupec, od `md` 2 sloupce pro lepší využití prostoru.

- AdminKpiOverview (upraveno – lokalizace)
  - Umístění: `app/Filament/Widgets/AdminKpiOverview.php`
  - Zdroje dat: počty uživatelů, hráčských profilů, týmů, zápasů, tréninků a záznamů docházky.
  - Všechny texty převedeny na překladové klíče.

- SystemHealthWidget
  - Umístění: `app/Filament/Widgets/SystemHealthWidget.php`
  - Šablona: `resources/views/filament/widgets/system-health-widget.blade.php`
  - Funkce: indikace běhu plánovače (Cron) a stavu AI indexu (best‑effort; bezpečné fallbacky, pokud modely/tabulky nejsou k dispozici).

- RecentActivityWidget
  - Umístění: `app/Filament/Widgets/RecentActivityWidget.php`
  - Šablona: `resources/views/filament/widgets/recent-activity-widget.blade.php`
  - Funkce: přehled posledních 3 záznamů z auditního logu (bezpečné fallbacky při absenci modelu/tabulky).

- FinanceOverview (upraveno – lokalizace)
  - Umístění: `app/Filament/Widgets/FinanceOverview.php`
  - Pozn.: Widget není nyní na dashboardu registrován (viz níže), ale je připraven s překlady pro případné nasazení.

#### Registrace na panelu
- Soubor: `app/Providers/Filament/AdminPanelProvider.php`
- Nový seznam dashboard widgetů:
  - `WelcomeBannerWidget`, `AdminKpiOverview`, `SystemHealthWidget`, `RecentActivityWidget`
- Odstraněny: `AccountWidget`, `FilamentInfoWidget` (dle požadavku – nechceme na nástěnce odhlášení ani Filament panel).

#### Lokalizace
- Přidány soubory překladů pro dashboard:
  - `lang/cs/admin/dashboard.php`
  - `lang/en/admin/dashboard.php`
- Klíče pokrývají:
  - Uvítací hlavičku a rychlé akce
  - KPI (uživatelé, hráči, týmy, zápasy, tréninky, docházka)
  - Finance (připraveno pro FinanceOverview)
  - Stav systému (Cron, AI)
  - Poslední aktivity

#### UI a ikony
- Respektována projektová konvence pro Font Awesome (Light varianta) a integrace přes `IconHelper`/`FilamentIcon`.
- Vizuální konzistence s brandingem (použití klubových barev přes globální CSS variables v panelu).

#### Poznámky
- Všechny dotazy na doménové modely jsou ošetřeny pomocí `class_exists` a try/catch, aby widgety nepadaly v prostředí bez daných tabulek/dat.
- V případě potřeby je možné `FinanceOverview` znovu přidat do seznamu widgetů v `AdminPanelProvider`.

#### Návod k rozšíření
- Pro přidání dalších kontrol do `SystemHealthWidget` (např. fronty, úložiště) rozšiřte metodu `getViewData()` a šablonu.
- Pro úpravu rychlých akcí v `WelcomeBannerWidget` lze využít `getUrl('create')` na dalších Filament resourcích.
