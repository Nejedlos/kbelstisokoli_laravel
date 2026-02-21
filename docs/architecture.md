# Architektura projektu Kbelští sokoli

Tento dokument popisuje základní strukturu a organizaci kódu v projektu.

## 1. Logické rozdělení
Aplikace je rozdělena do tří hlavních oblastí:

1. **Public (Veřejný frontend):**
   - **Namespace:** `App\Http\Controllers\Public`, `App\ViewModels\Public`
   - **Routy:** `routes/public.php` (bez prefixu)
   - **Layout:** `resources/views/layouts/public.blade.php`
   - **Účel:** Hlavní web pro veřejnost, soupisky, výsledky, aktuality.

2. **Member (Členská sekce):**
   - **Namespace:** `App\Http\Controllers\Member`, `App\ViewModels\Member`
   - **Routy:** `routes/member.php` (prefix `/clenska-sekce`)
   - **Layout:** `resources/views/layouts/member.blade.php`
   - **Účel:** Sekce pro hráče a trenéry, docházka, individuální plány, vnitřní oznamy.

3. **Admin (Administrace):**
   - **Namespace:** `App\Http\Controllers\Admin` (pro vlastní controllers), Filament Resource
   - **Routy:** `routes/admin.php` (prefix `/admin/custom`), Filament trasy
   - **Layout:** Filament Admin Layout / `resources/views/layouts/admin.blade.php`
   - **Účel:** Správa obsahu, správa členů, ekonomika, konfigurace systému.

## 2. Adresářová struktura (Klíčové části)
- `app/Http/Controllers/`: Rozděleno na `Public`, `Member`, `Admin`.
- `app/Http/Requests/`: Rozděleno na `Public`, `Member`, `Admin`.
- `app/ViewModels/`: Rozděleno na `Public`, `Member`, `Admin`.
- `app/Services/`: Aplikační logika a externí integrace.
- `app/Filament/`: Konfigurace a zdroje administrativního rozhraní.
- `routes/`:
    - `web.php`: Základní směrování a systémové trasy.
    - `public.php`: Veřejné trasy.
    - `member.php`: Trasy pro přihlášené členy.
    - `admin.php`: Vlastní trasy pro administraci (mimo Filament).

## 3. Konvence rout a přístupů
- Pojmenování rout využívá prefixy pro jasné odlišení oblastí:
  - Public: `public.*` (např. `public.home`, `public.news.index`, `public.matches.show`)
  - Member: `member.*` (např. `member.dashboard`, `member.attendance.index`, `member.profile.edit`)
  - Admin: `admin.*` (např. `admin.dashboard`, `admin.content.index`)
- URL prefixy a middleware skupiny:
  - Public: bez speciálního prefixu (v rámci `web`)
  - Member: prefix `/clenska-sekce`, skupina `member` (`auth`, `verified`, `permission:view_member_section`)
  - Admin (custom mimo Filament): prefix `/admin/custom`, skupina `admin` (`auth`, `permission:access_admin`)

## 4. Navigace (menu)
- Struktura menu je centralizovaná v `config/navigation.php` a je rozdělena na části:
  - `public`: hlavní veřejné menu
  - `member.header`, `member.sidebar`: menu pro členskou sekci
  - `admin`: menu pro vlastní admin stránky (mimo Filament)
- Layout šablony načítají menu dynamicky:
  - `resources/views/layouts/public.blade.php`
  - `resources/views/layouts/member.blade.php`
  - `resources/views/layouts/admin.blade.php`

## 5. Technologie
- **Framework:** Laravel 12
- **Administrace:** Filament PHP 5
- **Routing:** Laravel Folio (pro určité části) & Standardní routování
- **Stylování:** Tailwind CSS přes Vite

## 5. Autentizace a oprávnění
- **Autentizace:** Laravel Fortify (základní login, 2FA připraveno)
- **Role & oprávnění:** Spatie Laravel Permission
- **Základní role:** `admin`, `coach`, `editor`, `player`, `parent`
- **Základní oprávnění:**
  - `access_admin` (přístup do administrace a vlastních admin rout)
  - `manage_users`, `manage_content`, `manage_teams`, `manage_attendance`
  - `view_member_section` (přístup do členské sekce)
  - `use_raw_html` (vkládání surového HTML v Page Builderu)
  - `manage_advanced_settings` (pokročilé nastavení bloků a vkládání head/footer kódů)
- **Přiřazení (výchozí skeleton):**
  - `admin`: všechna oprávnění (včetně `use_raw_html`, `manage_advanced_settings`)
  - `editor`: `access_admin`, `manage_content`
  - `coach`: `access_admin`, `manage_teams`, `manage_attendance`, `view_member_section`
  - `player`: `view_member_section`
  - `parent`: `view_member_section`
- **Middleware skupiny:** definované v `bootstrap/app.php`
  - `member`: `auth`, `verified`, `permission:view_member_section`
  - `admin`: `auth`, `permission:access_admin`
- **Routy:**
  - Public: `routes/public.php` (`/uvod` → `resources/views/public/home.blade.php`)
  - Member: `routes/member.php` (prefix `/clenska-sekce`, route `member.dashboard`)
  - Admin (custom): `routes/admin.php` (prefix `/admin/custom`, route `admin.custom.dashboard`)

## 7. Page Builder (Blokový systém)
- Účel: Umožnit správcům sestavovat stránky z předdefinovaných vizuálně konzistentních bloků.
- **UX pro laiky:** 
    - Každý blok má nápovědné texty a srozumitelné popisky.
    - Omezení na předdefinované varianty (styly, rozvržení) zajišťuje branding.
    - Drag & drop řazení s náhledy (itemLabels) pro snadnou orientaci.
    - Bezpečnost: Surové HTML je dostupné pouze pro roli Admin s patřičným oprávněním.
    - **Expert UX (Superadmin):** 
        - Každý blok obsahuje skrytou sekci "Pokročilé", kde lze definovat vlastní CSS třídy, ID a HTML atributy.
        - Stránky a novinky mají záložku "Vývojář" pro vkládání vlastních skriptů do `<head>` a před `</body>`.
        - Tato pole jsou viditelná pouze s oprávněním `manage_advanced_settings`.

### Technické řešení
- **Datový model:**
    - `page_blocks`: tabulka pro ukládání bloků (vazba na `page_id`, připravena pro budoucí polymorfní použití).
    - `pages.content`: JSON sloupec využívaný pro přímou integraci s Filament Builderem.
- **JS Knihovny:**
    - `SortableJS`: zajišťuje drag & drop řazení bloků (integrováno ve Filamentu).
    - `Tiptap`: moderní core pro rich-text editor (připraveno pro budoucí hlubší integraci, aktuálně využit standardní RichEditor).
- **Centrální registr:** `App\Services\Cms\BlockRegistry` definuje schémata a ikony pro všech 11 typů bloků.

### Podporované typy bloků
1. **Hero sekce:** varianty (standard, centered, minimal), nadpis, podnadpis, CTA, obrázek, zarovnání.
2. **Textový blok:** formátovaný text (Tiptap/RichEditor).
3. **Obrázek:** nastavení šířky, popisek, alt text.
4. **CTA:** styl (primární, sekundární, outline), odkaz.
5. **Mřížka karet:** volba počtu sloupců (2-4), opakovatelné karty.
6. **Statistické údaje:** číselné hodnoty s popisky a ikonami.
7. **Výpis novinek:** nastavení limitu, kategorie a rozvržení (mřížka/seznam).
8. **Výpis zápasů:** typ (nadcházející/poslední), limit.
9. **Galerie:** styl (mřížka/masonry), hromadný upload.
10. **Kontakt / Info:** adresa, kontakty, volitelná interaktivní mapa.
11. **Vlastní HTML:** módy Safe Embed (všem) vs Raw HTML (pouze admin).

### Renderování (Frontend)
- Komponenta `<x-page-blocks :blocks="$page->content" />` prochází pole bloků a inkluduje příslušné Blade šablony z `resources/views/public/blocks/`.
- Každá šablona má přístup k proměnné `$data` obsahující payload bloku.

### Jak přidat nový typ bloku
1. Přidejte metodu a registraci v `App\Services\Cms\BlockRegistry`.
2. Vytvořte Blade šablonu v `resources/views/public/blocks/{slug}.blade.php`.
3. Spusťte `npm run build` (pokud měníte styly).

## 8. Sportovní modul
Účel: Správa sportovních dat oddílu (týmy, zápasy, tréninky, sezóny).

### Datový model (ER)
- **Seasons (Sezóny):** Název (např. 2024/25), aktivní stav.
- **Teams (Týmy):** Kategorie (např. U11, Muži), popis, slug.
- **Opponents (Soupeři):** Název, město, logo.
- **BasketballMatches (Zápasy):** Vazba na tým, sezónu a soupeře. Pole: datum, místo, skóre, stav (plánováno, odehráno...), domácí/hosté.
- **Trainings (Tréninky):** Jednotlivé termíny tréninků pro týmy. Příprava na docházku.
- **Events (Akce):** Klubové akce mimo běžný režim (soustředění, schůze).

### Vztahy
- `BasketballMatch` patří pod `Team`, `Season` a `Opponent`.
- `Training` patří pod `Team`.
- `Team` má mnoho `BasketballMatch` a `Training`.
- `Attendance` (Docházka) má polymorfní vazbu na `Training`, `BasketballMatch` a `ClubEvent`.

## 9. Docházka a RSVP (RSVP Modul)
Účel: Univerzální systém pro potvrzování účasti a evidenci docházky na všech typech klubových akcí.

### Datový návrh
- **Attendance Model:** Obsahuje `user_id`, `status` (pending, confirmed, declined, maybe), `note`, `internal_note` a polymorfní vazbu `attendable`.
- **Zvolené řešení:** Polymorfní vazba byla zvolena pro maximální rozšiřitelnost. Libovolný nový model (např. `Camp`, `Workshop`) lze do systému zapojit pouhým přidáním vztahu `morphMany` bez nutnosti měnit strukturu tabulky docházky.

### Workflow
1. **Hráč (Člen):**
   - V členské sekci (`/clenska-sekce/program`) vidí chronologický přehled všech nadcházejících událostí (tréninky, zápasy, akce).
   - Pomocí rychlých akcí (mobile-first) potvrdí nebo omluví svou účast.
   - U omluvenky může uvést důvod (uloží se do pole `note`).
2. **Trenér / Admin:**
   - V administraci u konkrétní události vidí tabulku `Docházka / RSVP`.
   - Má okamžitý přehled o počtech potvrzených hráčů.
   - Může doplňovat interní poznámky (např. "Hráč se omluvil telefonicky").
   - Má právo editovat nebo mazat záznamy všech členů.

### Jak přidat nový typ RSVP události
1. V modelu nové události přidejte vztah:
   ```php
   public function attendances(): \Illuminate\Database\Eloquent\Relations\MorphMany {
       return $this->morphMany(Attendance::class, 'attendable');
   }
   ```
2. Do Filament Resource přidejte `AttendancesRelationManager`.
3. V `AttendanceController` rozšiřte metodu `index` a `store` o nový typ (pokud se má zobrazovat v globálním programu).

## 10. Modul statistik a soutěží
Účel: Správa a zobrazení sportovních statistik a interních klubových soutěží.

### Datový model
- **StatisticSet:** Definice tabulky (např. "Ligová tabulka"). Obsahuje `column_config` (JSON) pro dynamické sloupce.
- **StatisticRow:** Jednotlivé řádky dat. Hodnoty jsou uloženy v JSON poli `values`. Vazba na `player`, `team`, `match` nebo `season`.
- **ExternalStatSource:** Konfigurace pro budoucí automatizované importy z externích URL.
- **ClubCompetition:** Interní soutěže (např. Lumír Trophy).
- **ClubCompetitionEntry:** Jednotlivé zápisy do soutěže (body, asistence) s možností kumulativního nebo absolutního započtení.

### AI Ingest Pipeline (Budoucí implementace)
Architektura je připravena na automatizovanou extrakci dat pomocí AI:
1. **Fetcher:** Stáhne HTML obsah z URL externí asociace.
2. **Extractor:** Vyhledá relevantní tabulku v HTML (na základě CSS selektorů).
3. **Normalizer (AI):** LLM transformuje surová HTML data do standardizovaného DTO formátu (`NormalizedTableDTO`).
4. **Importer:** Uloží data do příslušného `StatisticSet`.

Všechny tyto části mají definované rozhraní (Interfaces) v `app/Services/Stats/Contracts`.

### Administrace (Filament)
- **Dynamické formuláře:** `RowsRelationManager` automaticky generuje vstupní pole podle toho, jaké sloupce admin v sadě statistik definoval.
- **Leaderboardy:** Soutěže zobrazují automaticky seřazené pořadí účastníků.

### Zobrazení (Frontend)
- **Blok `stats_table`:** Umožňuje vložit libovolnou tabulku statistik do CMS stránky.
- **Komponenta `<x-leaderboard />`:** Pro vizuální zobrazení pořadí v klubových soutěžích.

### Administrace (Filament)
- Všechny entity jsou dostupné v navigační skupině **Statistiky**.
- Oprávnění: `manage_stats` (oficiální data), `manage_competitions` (klubové soutěže).

### Veřejné zobrazení
- `/zapasy`: Seznam všech zápasů s paginací.
- `/zapasy/{id}`: Detail zápasu s výsledkem a veřejnou poznámkou.
- `/treninky`: Přehled tréninkových informací rozdělený podle týmů.



## 11. User management a hráčské profily

Tato sekce popisuje implementaci správy uživatelů, rolí/oprávnění a hráčských profilů v administraci.

### 11.1 Oddělení User vs PlayerProfile
- User je primární entita pro přihlášení a autorizaci (Fortify + Spatie Permission).
- PlayerProfile je volitelný 1:1 profil navázaný na User a slouží k evidenci sportovních údajů hráče.
- Rozšiřitelnost: Lze přidat další typy profilů (např. CoachProfile, GuardianProfile) bez zásahu do jádra.

### 11.2 Datový model
- users: rozšířeno o `is_active` (bool), `phone` (string), `last_login_at` (timestamp), `admin_note` (text).
- player_profiles: `user_id` (unique, FK), `jersey_number`, `position` (PG/SG/SF/PF/C), `public_bio`, `private_note`, `is_active`, `metadata` (JSON).
- player_profile_team (pivot): `player_profile_id`, `team_id`, `role_in_team` (player/captain/assistant), `is_primary_team`, `active_from`, `active_to`.

### 11.3 Vztahy a využití
- `User -> playerProfile(): hasOne` (volitelné).
- `PlayerProfile -> teams(): belongsToMany` s pivot atributy pro týmovou roli a období.
- `Team -> players(): belongsToMany` pro rychlé dotazy.

### 11.4 Admin (Filament) – CRUD a UX
- UserResource:
  - Form: jméno, e‑mail, telefon, role (Spatie), heslo (bez povinnosti při editaci), `is_active`, interní poznámka.
  - Table: jméno, e‑mail, role (badge), aktivita (boolean), indikátor hráčského profilu, poslední přihlášení.
  - Filtrování: podle rolí, aktivity, existence hráčského profilu.
  - Bulk akce: Aktivovat/Deaktivovat (autorizováno přes `manage_users`).
  - RelationManager: `PlayerProfileRelationManager` (správa 1:1 profilu přímo z uživatele).
- PlayerProfileResource:
  - Form: výběr uživatele (pouze bez existujícího profilu), dres #, pozice (řízený výběr), bio, interní poznámka, přiřazení týmů (M:N), aktivita.
  - Table: uživatel, dres, pozice, týmy (badge), aktivita.

### 11.5 Role & Permissions (read‑only přehled)
- RoleResource (read‑only přehled): název role, počet uživatelů, seznam oprávnění (badge).
- PermissionResource (read‑only přehled): název oprávnění, přiřazené role.
- Policies: přístup vázán na `manage_users`; editace rolí/oprávnění povolena pouze roli `admin` (a omezení mazání systémových rolí).

### 11.6 Autorizace a bezpečnost
- Policies:
  - `UserPolicy`: všechny operace chráněny oprávněním `manage_users` (view own výjimka).
  - `PlayerProfilePolicy`: `manage_users` nebo `manage_teams` (trenéři), hráč vidí vlastní profil.
  - `RolePolicy`/`PermissionPolicy`: read‑only pro `manage_users`, úpravy rolí jen `admin`.
- Fortify login hook: `Fortify::authenticateUsing(...)` brání přihlášení deaktivovaným účtům.
- Middleware `active`: aplikován na skupiny `member` a `admin`; při zjištění neaktivního účtu provede odhlášení.
- `canAccessPanel()`: zohledňuje `is_active` pro přístup do Filament panelu.
- Listener `UpdateLastLoginAt`: nastaví `last_login_at` po úspěšném loginu.

### 11.7 Admin Dashboard (KPI shell)
- Widget `App\Filament\Widgets\AdminKpiOverview` zobrazuje karty: uživatelé (celkem/aktivní), hráčské profily, týmy, zápasy (celkem/nadcházející), tréninky (celkem/nadcházející), docházka (placeholder count).
- Widget je registrován v `AdminPanelProvider` (sekce `->widgets([...])`).

### 11.8 Přístupová pravidla (shrnutí)
- admin: plná správa users/roles/profiles, přístup na dashboard.
- coach: správa/čtení hráčských profilů a týmových informací (přes `manage_teams`), bez plné správy users/roles.
- editor: bez přístupu do user managementu (správa obsahu CMS).
- player: bez přístupu do admin user managementu; pouze členská sekce.
- deaktivovaný user: nepřihlásí se (login blokován) a middleware zamezí přístupu.

### 11.9 Napojení na další moduly
- Docházka/RSVP: vazba přes `users` (uživatel) a týmové členství přes `player_profiles ↔ teams`.
- Statistiky/Soutěže: použití `player_profiles` a `teams` pro identifikaci účastníků/řádků.
- Redirecty: Modul pro správu přesměrování (301/302) a legacy URL migraci.

### 11.10 CLI příkazy (neinteraktivní)
- Migrace a seed oprávnění:
```
php artisan migrate
php artisan db:seed --class=RoleSeeder
php artisan optimize:clear
```

## 12. Redirect manager a legacy migrace
Účel: Správa přesměrování (301/302) ze starého webu na nový a zajištění moderního UX pro chybové stavy.

### 12.1 Datový model
- `Redirect`: model pro definici přesměrování.
    - `source_path`: původní cesta (normalizováno s počátečním lomítkem).
    - `target_type`: `internal` (cesta na webu) nebo `external` (plná URL).
    - `status_code`: 301 (trvalé) nebo 302 (dočasné).
    - `match_type`: `exact` (přesná shoda) nebo `prefix` (začíná na).
    - `hits_count`: statistika využití přesměrování.

### 12.2 Redirect Resolution
- Logika je implementována v `RedirectMiddleware`, který běží ve `web` skupině.
- Vyhodnocuje se před standardním routováním Laravelu (nebo jako fallback před 404).
- Pokud middleware najde aktivní redirect pro aktuální cestu, provede přesměrování s příslušným kódem a inkrementuje statistiku.
- Obsahuje anti-loop ochranu (zamezení redirectu na stejnou URL).

### 12.3 Chybové stránky (Error UX)
- Vytvořeny moderní sportovní šablony v `resources/views/errors/`:
    - `404.blade.php`: Stránka nenalezena s užitečnými odkazy na sekce webu.
    - `403.blade.php`: Přístup odepřen (sportovní paralela s faulem).
    - `410.blade.php`: Obsah trvale odstraněn.

### 12.4 Legacy Migrace
- Připravena služba `App\Services\Cms\RedirectImporter`, která umožňuje hromadný import redirectů z polí/souborů.
- Podporuje normalizaci cest a detekci externích URL.

## 17. Členský portál (Member Section)
Účel: Osobní zóna pro hráče a trenéry pro správu docházky, profilu a týmových informací.

### 17.1 Architektura a přístup
- **URL prostor:** `/clenska-sekce` (v kódu route name prefix `member.`).
- **Middleware:** Skupina `member` (`auth`, `verified`, `active`, `permission:view_member_section`).
- **Layout:** `resources/views/layouts/member.blade.php` – mobile-first design, postranní navigace (desktop), spodní lišta (mobil).

### 17.2 Funkcionality
1. **Dashboard:** Role-aware rozcestník. Zobrazuje nejbližší program, stav docházky (pending) a týmy. Trenéři vidí navíc rychlé odkazy na své týmy.
2. **Můj program:** Sjednocený chronologický přehled tréninků, zápasů a akcí.
3. **RSVP / Docházka:** Rychlé potvrzení účasti (Ano/Ne/Možná) přímo z přehledu. Historie odpovědí je dostupná v samostatné sekci.
4. **Můj profil:** Možnost editace jména, telefonu, veřejného bio a změna hesla. Citlivé údaje (role, týmy) jsou pouze pro čtení.
5. **Ekonomika (Shell):** Připravený modul pro přehled plateb a členských příspěvků s informacemi o bankovním spojení.
6. **Týmové přehledy (Trenér):** Přehled docházky celého týmu na blížící se akce (vyžaduje oprávnění `manage_teams`).

### 17.3 Datové napojení
- Čerpá data z modulů: `Users`, `PlayerProfiles`, `Attendance`, `BasketballMatches`, `Trainings`, `ClubEvents`.
- Využívá `BrandingService` pro konzistentní barvy a zástupné symboly (hashe).

### 17.4 UI Komponenty
- `x-member.kpi-card`: Karty pro číselné přehledy na dashboardu.
- `x-member.event-card`: Komponenta pro událost s integrovanými RSVP formuláři.

### 17.5 CLI a vývoj
```bash
# Vyčištění cache po změnách v navigaci nebo layoutu
php artisan optimize:clear
```

### 17.6 Doporučené commity
1) feat(member): implement mobile-first layout and member portal navigation
2) feat(member): add role-aware dashboard and unified event overview
3) feat(member): implement profile editing and coach team overviews

## 18. Media Layer a Galerie

Účel: Centrální správa médií (obrázků, dokumentů) a galerií s integrací do Page Builderu a veřejného webu.

### 18.1 Architektura
- **MediaAsset:** Centrální model pro všechna média. Využívá `Spatie Media Library` pro fyzické ukládání souborů a generování konverzí (náhledů).
- **Gallery:** Model pro kolekce médií. Umožňuje definovat název, popis, cover a styl zobrazení.
- **GalleryMedia:** Vazební tabulka (M:N) s podporou řazení a override popisků pro konkrétní galerie.

### 18.2 Admin správa (Filament)
- **Knihovna médií:** Přehled všech nahraných souborů s náhledy, správou metadat (Alt text, Title) a úrovní přístupu (Public/Member/Private).
- **Galerie:** Správa alb, kde lze snadno připojovat assety z knihovny, měnit jejich pořadí (Drag & Drop) a nastavovat viditelnost.

### 18.3 Integrace do Page Builderu
Bloky byly upraveny tak, aby místo přímého nahrávání souborů využívaly vazbu na ID z knihovny médií:
- **Hero sekce:** Výběr obrázku na pozadí z knihovny.
- **Obrázek:** Zobrazení konkrétního assetu s volitelným popiskem.
- **Galerie:** Výběr existující galerie ze systému a volba layoutu (Grid/Masonry).

### 18.4 Veřejné zobrazení
- **URL prostor:** `/galerie` (listing) a `/galerie/{slug}` (detail).
- **Helpery:**
    - `media_url($id, $conversion)` – získá URL k souboru s volitelnou konverzí.
    - `media_alt($id)` – získá alternativní text pro SEO.
- **Fallbacky:** Pokud asset nebo galerie neexistuje, systém zobrazí sjednocený prázdný stav (`x-empty-state`).

### 18.5 Přístup a bezpečnost
- **Úrovně přístupu:** Každý asset má definovaný `access_level`. Public frontend automaticky ignoruje `member` a `private` assety (zatím na úrovni aplikační logiky).
- **Validace:** Při nahrávání jsou vynuceny rozumné limity na velikost a typy souborů (obrázky, PDF).

### 18.6 CLI a vývoj
```bash
# Vyčištění cache pro zaregistrování helperů a rout
composer dump-autoload
php artisan optimize:clear
```

### 18.7 Doporučené commity
1) feat(media): add central MediaAsset and Gallery models with Spatie integration
2) feat(admin): implement Media Library and Gallery resources in Filament
3) feat(public): add gallery listing/detail views and Page Builder integration

## 19. Komunikace a notifikace

Účel: Správa veřejných oznámení (bannerů) a doručování cílených notifikací členům klubu přes in-app a emailové kanály.

### 19.1 Oznámení (Announcements)
- **Model:** `Announcement` (tabulka `announcements`).
- **Funkce:** Horní lišta (Announcement Bar) na webu pro důležité zprávy.
- **Parametry:** Titulek, zpráva, CTA (tlačítko s odkazem), publikum (veřejnost/členové/všichni), stylová varianta (info/success/warning/urgent) a časová platnost.
- **Admin:** Filament Resource v sekci "Komunikace".
- **Frontend:** Komponenta `x-announcement-bar` integrovaná v public i member layoutu.

### 19.2 In-app Notifikace (Member Portal)
- **Technologie:** Vestavěný systém Laravel Notifications využívající `database` kanál.
- **Notifikační centrum:** Sekce v členské zóně (`/clenska-sekce/notifikace`) s výpisem zpráv, stavem přečtení a možností hromadného označení za přečtené.
- **Badge:** Ikona zvonku v horní navigaci s dynamickým počtem nepřečtených zpráv.

### 19.3 Emailové notifikace
- **BaseNotification:** Abstraktní třída zajišťující sjednocený vzhled emailů s brandingem klubu.
- **Šablony:** Publikované a upravené Blade šablony v `resources/views/vendor/notifications`.
- **Kanály:** Automatické doručování přes `mail` a `database` na základě typu zprávy a uživatelských preferencí.

### 19.4 Architektura a integrace
- **Event-driven:** Notifikace jsou spouštěny pomocí Laravel Eventů (např. `RsvpChanged`).
- **CommunicationService:** Centrální služba pro načítání oznámení s cachováním pro maximální výkon.
- **User Preferences:** Každý uživatel má v DB JSON sloupec `notification_preferences` pro individuální nastavení doručování (skeleton).

### 19.5 CLI a vývoj
```bash
# Vyčištění cache oznámení a rout
php artisan optimize:clear
```

### 19.6 Doporučené commity
1) feat(comm): add Announcement model and Filament admin resource
2) feat(notifications): implement member notification center and unread badge
3) feat(logic): add event-driven RSVP notifications and BaseNotification branding


## 20. Scheduler a provozní automatizace (Cron Modul)

Účel: Centralizovaná správa plánovaných úloh, automatizace workflow a monitoring provozu systému.

### 20.1 Architektura
- **Dynamický Scheduler:** Úlohy jsou definovány v databázi (tabulka `cron_tasks`) a dynamicky registrovány do Laravel scheduleru v `bootstrap/app.php`.
- **Job Wrapper:** Všechny Artisan příkazy jsou spouštěny přes `RunCronTaskJob`, který zajišťuje izolaci a detailní logování každého běhu.
- **Logování:** Každý pokus o spuštění, jeho výstup, trvání a případné chyby jsou ukládány do tabulky `cron_logs`.

### 20.2 Webový trigger (Cron URL)
Pro prostředí, kde není možné nastavit systémový cronjob, je připraven webový trigger:
- **URL:** `/system/cron/run?token=VAŠ_TOKEN`
- **Bezpečnost:** Vyžaduje `CRON_TOKEN` definovaný v `.env`. Bez platného tokenu není spuštění scheduleru povoleno.

### 20.3 Implementované úlohy (Skeletony)
1.  **RSVP Upomínky (`rsvp:reminders`):** Vyhledává členy, kteří nepotvrdili účast na akcích začínajících v příštích 24 hodinách.
2.  **Sync oznámení (`announcements:sync`):** Automaticky deaktivuje (přepne `is_active` na `false`) oznámení, kterým vypršel čas platnosti.
3.  **Import statistik (`stats:import`):** Vstupní bod pro budoucí automatizovanou pipeline stahování dat z externích webů.
4.  **Systémový úklid (`system:cleanup`):** Pravidelné promazávání starých logů (standardně starší než 30 dní) pro úsporu místa v DB.

### 20.4 Admin správa (Filament)
Resource **Cron tabulka** v sekci "Nastavení":
- **Správa úloh:** Editace cron výrazů, Artisan příkazů a priorit.
- **Ruční spuštění:** Tlačítko "Spustit nyní" přidá úlohu okamžitě do fronty k provedení.
- **Audit:** Detailní historie všech běhů s náhledem na textový výstup příkazu.

### 20.5 Provozní nastavení
- **Log Retention:** Doba uchování logů je nastavitelná v `config/system.php` (výchozí 30 dní).
- **Idempotence:** Úlohy jsou navrženy tak, aby jejich opakované spuštění nezpůsobilo chybu nebo duplicitní data.

### 20.6 CLI a vývoj
```bash
# Inicializace základních úloh
php artisan db:seed --class=CronTaskSeeder

# Ruční spuštění scheduleru
php artisan schedule:run

# Spuštění queue workera (nutné pro RunCronTaskJob)
php artisan queue:work
```

### 20.7 Doporučený produkční setup
Pro plnou funkčnost automatizace nastavte na serveru cronjob:
```cron
* * * * * cd /cesta/k/projektu && php artisan schedule:run >> /dev/null 2>&1
```
Nebo využijte webový trigger nastavením externí služby na volání URL každou minutu.

Cíl: Vytvořit konzistentní, hypermoderní a sportovní veřejný frontend řízený backendem (global settings + page builder), mobile‑first, bez seed obsahu.

### 13.1 Co bylo vytvořeno / upraveno
- Design tokens a utility v `resources/css/app.css` (Tailwind CSS v4, `@theme`).
- Komponenty: `x-header`, `x-footer`, `x-section-heading`, `x-empty-state`.
- Layout: `resources/views/layouts/public.blade.php` (napojení na branding + navigaci, mobile-first).
- Renderer bloků: `resources/views/components/page-blocks.blade.php` (respekt `is_visible`, fallback unknown).
- Úpravy šablon bloků pro konzistenci: `hero`, `cards_grid`, `news_listing`, `matches_listing`, `fallback`.
- Moderní error pages: `resources/views/errors/{403,404,410}.blade.php` v novém stylu.
- Branding vrstva: `config/branding.php` + `App\Services\BrandingService` + view composer v `AppServiceProvider`.

### 13.2 Design system (principy, varianty, patterns)
- Tokens (`@theme`):
  - Barvy: `--color-primary`, `--color-secondary`, `--color-dark`, `--color-light` (utility např. `bg-primary`, `text-primary`).
  - Typografie: `--font-sans` (default), `--font-display` pro nadpisy.
  - Radius, stíny: `--radius-club` (`rounded-club`), `--shadow-club` (`shadow-club`).
  - Spacing: `--spacing-club` pro sekční odsazení (`.section-padding`).
- Reusable patterns:
  - Buttons: `.btn`, `.btn-primary`, `.btn-outline`.
  - Cards: `.card`, `.card-hover`.
  - Section heading: `<x-section-heading />` (title, subtitle, align, CTA).
  - Empty state: `<x-empty-state />` (title, subtitle, CTA, icon).

### 13.3 Render pipeline pro Page Builder bloky
- Vstup: pole bloků (Filament Builder: `type` + `data`).
- Renderer: `<x-page-blocks :blocks="$page->content" />` iteruje, respektuje `data.is_visible` a přidává `section-padding`.
- Pokročilé nastavení (Expert): `custom_id`, `custom_class`, `custom_attributes` – atributy se přenesou do wrapperu bloku.
- Fallback: `public.blocks.fallback` pro neznámé/nezpracované bloky (bez pádu aplikace).

### 13.4 Napojení na global settings / branding
- Konfigurace `config/branding.php` (bez seed obsahu), možnost napojit na env proměnné.
- `BrandingService` poskytuje bezpečné fallbacky; připojeno do `layouts.public` přes View composer (`AppServiceProvider`).
- Header/Footer komponenty čtou `branding` a `config('navigation.public')`. Sekce se nezobrazí, pokud data chybí.

### 13.5 Fallbacky a chybové/empty stavy
- Error pages 403/404/410 v novém designu, s rozcestníkem na klíčové sekce.
- Empty states přes `<x-empty-state />` (žádné novinky, zápasy, atd.).
- Unknown block fallback s decentní kartou a vysvětlením.
- Chybějící branding hodnoty nezpůsobí pád – části UI se skryjí.

### 13.6 Jak přidat nový frontend block renderer / komponentu
1. Přidejte šablonu do `resources/views/public/blocks/{block}.blade.php` a držte se design systemu (container, section-padding, btn/card utilit).
2. Pokud je to nový typ, zaregistrujte ho v `App\Services\Cms\BlockRegistry` (admin Builder).
3. Pro společné nadpisy použijte `<x-section-heading />`, pro prázdný stav `<x-empty-state />`.
4. V případě potřeby doplňte tokeny do `resources/css/app.css` a spusťte build.

### 13.7 Příkazy (npm/composer/artisan)
```
# Instalace lehké JS knihovny pro interakce
npm install alpinejs

# Build assetů (Vite)
npm run build

# Vyčištění cache (po přidání configu/komponent)
php artisan optimize:clear
```

### 13.8 Doporučené commity (conventional commits)
1. `feat(frontend): introduce Tailwind v4 design tokens and base UI components`
2. `feat(public): add header/footer layout, page blocks renderer and modern error pages`
3. `feat(cms): align public block templates with design system and add empty states`

### 13.9 Next step (doporučení)
- Napojit `Header/Footer` na skutečné menu z DB (resource `Menu` + `menu_items`) s cache vrstvou.
- Rozšířit renderery zbývajících bloků (rich text, image, CTA, gallery) o varianty a empty states.
- Přidat lazy‑loading obrázků a `aspect-ratio` helpers (Media Library integrace).
- (Volitelně) Doplnit vlastní fonty přes Vite a preload.

## 14. Branding Presets a Design Tokens
Účel: Umožnit snadnou změnu barevného schématu a identity webu bez nutnosti zásahu do kódu.

### 14.1 Theme Preset Architektura
- Systém podporuje více předdefinovaných témat (např. `club-default`, `dark-arena`, `light-clean`).
- Konfigurace témat je uložena v `config/branding.php`.
- Každé téma definuje sadu barev pro brand (navy, blue, red, white) a UI (bg, surface, border, text).

### 14.2 Design Tokens a CSS Proměnné
- Frontend nevyužívá pevné hex barvy, ale CSS custom properties (např. `--color-brand-red`).
- Tyto proměnné jsou dynamicky generovány v `BrandingService` na základě aktivního tématu.
- `layouts.public` obsahuje `<style>` tag s těmito proměnnými v sekci `:root`.
- `app.css` mapuje Tailwind tokens (`primary`, `secondary`, `bg`, atd.) na tyto CSS proměnné.

### 14.3 Administrace (Branding Settings)
- Stránka **Branding a vzhled** v sekci Nastavení umožňuje:
    - Výběr barevného tématu (Presetu).
    - Nastavení základní identity (názvy, slogany).
    - Nahrávání log (hlavní a alternativní).
    - Správu kontaktních údajů a sociálních sítí pro patičku.
    - Konfiguraci globálního CTA tlačítka.
- Nastavení jsou ukládána do tabulky `settings` (klíč-hodnota).

### 14.4 Ochrana vizuální konzistence
- Admin nemůže zadávat libovolné barvy, vybírá pouze z otestovaných presetů.
- Pokud nastavení v DB chybí, systém automaticky fallbackuje na hodnoty v `config/branding.php`.
- Cache pro nastavení brandingu se automaticky promazává při uložení změn.

### 14.5 Zástupné symboly (Hashe)
V CMS obsahu, sloganech a textech lze používat hashe, které se dynamicky mění podle nastavení klubu:
- `###TEAM_NAME###` – Plný název klubu (např. Kbelští sokoli C & E).
- `###TEAM_SHORT###` – Zkrácený název (např. Sokoli).
- `###CLUB_NAME###` – Alias pro plný název.

Tyto hashe jsou automaticky nahrazovány v:
- Page Builder blocích.
- Detailu novinek (perex, obsah).
- Hlavičce a patičce (slogany, texty).
- Meta tazích (SEO).

Pro nahrazení v kódu slouží helper `brand_text($text)`.

### 14.6 Jak přidat nový preset
1. V `config/branding.php` přidejte nový klíč do pole `themes`.
2. Definujte název (`label`) a paletu barev (`colors`).
3. Nový preset se okamžitě objeví v administraci ve výběru.

### 14.7 Příkazy
```
# Migrace pro tabulku nastavení
php artisan migrate
# Vyčištění cache po změně brandingu (pokud nebylo provedeno přes admin)
php artisan optimize:clear
```

### 14.8 Doporučené commity
1. `feat(branding): add settings table and theme preset configuration`
2. `feat(branding): implement BrandingService with dynamic CSS variable generation`
3. `feat(admin): create BrandingSettings page for easy identity management`
4. `feat(branding): add dynamic placeholder hash system for club names`

## 15. Veřejné šablony a stránky (Public Frontend)

Účel: Poskytnout návštěvníkům konzistentní, moderní a sportovní zážitek s plnou integrací na CMS a sportovní moduly.

### 15.1 Routing a Strategie
- **Systémové stránky:** Pevně definované cesty (`/novinky`, `/zapasy`, `/kontakt`).
- **CMS Stránky:** Dynamické slugy (`/{slug}`) s fallbackem. Systémové cesty mají přednost.
- **Redirects:** Integrovaný `RedirectMiddleware` vyhodnocuje přesměrování před vyhozením 404.

### 15.2 SEO a Metadata
- **Dynamické tagy:** `layouts.public` obsahuje meta tagy pro SEO (title, description, keywords) a Open Graph (og:title, og:image, atd.).
- **Fallbacky:** Pokud data v modelu (`seo` vztah) chybí, použijí se výchozí hodnoty z brandingu nebo název entity.
- **Controller integrace:** `NewsController` a `PageController` proaktivně načítají SEO metadata.

### 15.3 Klíčové komponenty
- `x-page-header`: Jednotné záhlaví pro systémové stránky s podporou drobečkové navigace (breadcrumbs) a obrázků na pozadí.
- `x-news-card`: Karta článku s náhledem, kategorií a datem.
- `x-match-card`: Sportovní karta zápasu s výsledkem, termínem a vizuálním odlišením (doma/venku).
- `x-empty-state`: Jednotný design pro prázdné seznamy nebo nenalezená data.

### 15.4 Implementované stránky
- **Novinky:** Přehled s paginací a detail s podporou Tailwind Typography (`prose`).
- **Zápasy:** Match Center s výsledkovou tabulí, informacemi o srazu a barvě dresů. Filtry na Nadcházející/Odehrané.
- **Týmy:** Seznam kategorií (Mládež/Dospělí) se sportovními kartami týmů.
- **Kontakt:** Plně řízený z `BrandingService` (adresy, maily, sociální sítě).

### 15.5 Doporučené commity
1. `feat(frontend): implement public shared components and dynamic seo layout`
2. `feat(public): add news and match center templates with sports branding`
3. `feat(public): implement team listing and contact page with branding integration`

## 16. Režim přípravy (Under Construction)
Účel: Stylové zobrazení informace o přípravě webu s basketbalovou tématikou.

### 16.1 Vizuální pojetí
- **Design:** Stránka je navržena jako samostatné HTML bez menu (nezávislá na `layouts.public`).
- **Téma:** Basketbalová taktická tabule ("TIME-OUT!") s křídovými čárami (X a O) a animovaným míčem.
- **Humor:** Využívá basketbalovou terminologii ("Trenér kreslí taktiku", "Lakujeme palubovku").
- **Branding:** Plně integrované barvy přes CSS proměnné z `BrandingService`.

### 16.2 Aktivace a kontrola
- **Manuální:** V administraci (Nastavení -> Branding) lze režim zapnout/vypnout.
- **Automatická:** Pokud v databázi neexistuje žádná publikovaná a viditelná `Page`, web se do tohoto režimu přepne automaticky.
- **Middleware:** `PublicMaintenanceMiddleware` zajišťuje přesměrování všech veřejných rout na domovskou stránku, pokud je režim aktivní.
### 16.3 Bypass pro adminy
- **Princip:** Přihlášení uživatelé s oprávněním `access_admin` (role Admin, Editor, Coach) vidí frontend v plné podobě i v případě, že je režim přípravy aktivní nebo chybí obsah.
- **Implementace:** Bypass je ošetřen v `PublicMaintenanceMiddleware` (pro podstránky) a v `HomeController` (pro domovskou stránku). To umožňuje týmu připravovat a testovat web před jeho oficiálním spuštěním.
### 16.4 Doporučené commity
1. `feat(public): implement funny basketball-themed under construction page`
2. `refactor(home): pass branding css variables to maintenance view`
3. `feat(admin): update maintenance mode defaults and hints in branding settings`
4. `feat(maintenance): add admin bypass for under construction mode`

## 21. Finance a členské příspěvky

Účel: Evidence členských příspěvků, poplatků za akce a plateb s automatickým párováním a notifikacemi.

### 21.1 Datový model (Trojvrstvá architektura)
1. **FinanceCharge (Předpisy):** Co má člen zaplatit (částka, splatnost, typ).
2. **FinancePayment (Platby):** Co bylo skutečně přijato (bankovní převod, hotovost, VS).
3. **ChargePaymentAllocation (Alokace):** Vazební tabulka, která definuje, kolik z konkrétní platby bylo použito na konkrétní předpis.

Tento přístup umožňuje:
- Částečné úhrady předpisů.
- Jednu platbu rozdělit mezi více předpisů (např. sourozenec nebo více poplatků najednou).
- Sledování přeplatků a historie úhrad.

### 21.2 Statusy a automatizace
Status předpisu (`FinanceCharge`) je automaticky synchronizován:
- `draft`: Rozpracovaný, člen ho nevidí.
- `open`: K úhradě, dosud nic nealokováno.
- `partially_paid`: Částečně uhrazeno.
- `paid`: Plně uhrazeno (součet alokací >= celková částka).
- `overdue`: Po splatnosti (automaticky přepínáno úlohou `finance:sync`).
- `cancelled`: Zrušeno adminem.

### 21.3 Členská sekce (Economy)
Členové vidí ve svém portálu pouze položky s příznakem `is_visible_to_member`:
- KPI karty (Celkem k úhradě, Po splatnosti, Uhrazeno).
- Seznam otevřených položek s detaily.
- Historii plateb a plně uhrazených předpisů.
- Platební instrukce (bankovní účet).

### 21.4 Notifikace
- **Nová platba:** Při vytvoření předpisu je členovi odeslána notifikace `NewChargeNotification` (in-app + email).
- **Upomínky:** Systém je připraven na automatické upomínky před splatností (přes `finance:sync` úlohu).

### 21.5 CLI a operace
```bash
# Ruční synchronizace statusů a splatnosti
php artisan finance:sync

# Sledování finančních KPI v administraci
# (Widget FinanceOverview na dashboardu)
```

### 21.6 Doporučené commity
1) feat(finance): implement charges, payments and allocations data model
2) feat(admin): add finance management resources and allocation system
3) feat(member): connect economy section to real financial data and summaries
4) feat(finance): add automated status sync and new charge notifications

## 22. Autentizace a bezpečnost

Účel: Zajištění bezpečného přístupu k systému s různou úrovní rizika pro veřejnost, členy a administrátory.

### 22.1 Bezpečnostní zóny
1. **Veřejná zóna:** Bez nutnosti přihlášení.
2. **Členská zóna (Member):** Vyžaduje standardní přihlášení (e-mail + heslo). Dvoufázové ověření (2FA) je pro běžné členy volitelné.
3. **Administrace (Admin):** Vyžaduje přihlášení a **povinné** dvoufázové ověření (2FA). Bez aktivního 2FA je přístup do administrace blokován middlewarem.

### 22.2 Technologie a komponenty
- **Laravel Fortify:** Zajišťuje backend pro login, logout, reset hesla a 2FA (TOTP).
- **Spatie Laravel Permission:** Řízení přístupu na základě rolí a oprávnění.
- **Middleware `2fa.required`:** Kontroluje, zda mají administrátoři aktivní 2FA.
- **Onboarding systém:** Sleduje stav aktivace účtu přes pole `onboarding_completed_at`.

### 22.3 Klíčová workflow

#### 1. Registrace a onboarding
- **Veřejná registrace je zakázána.** Účty může vytvářet pouze administrátor.
- Nově vytvořený účet nemá nastavené heslo a má prázdné pole `onboarding_completed_at`.
- Administrátor odešle "Pozvánku" (akce v seznamu uživatelů), která uživateli zašle bezpečný odkaz pro nastavení hesla.
- Dokončením resetu hesla se nastaví `onboarding_completed_at` a účet je považován za aktivovaný.

#### 2. Přihlášení a 2FA
- Pokud uživatel s přístupem do administrace nemá aktivní 2FA, je po přihlášení přesměrován do svého profilu s výzvou k aktivaci.
- 2FA využívá standard TOTP (kompatibilní s Google Authenticator, Authy, Microsoft Authenticator).
- Při ztrátě zařízení lze použít vygenerované záchranné kódy (Recovery Codes).

#### 3. Bezpečnostní audit
- Všechny klíčové události (úspěšný login, selhání, reset hesla, změna 2FA) jsou logovány přes `SecurityLogger` do denního logu s prefixem `SECURITY_EVENT`.
- Logování obsahuje IP adresu a User Agent pro detekci podezřelé aktivity.

### 22.4 Middleware a enforcement
- `auth`: Základní autentizace.
- `active`: Kontrola, zda účet není deaktivován adminem (`is_active`).
- `2fa.required`: Vynucení 2FA pro role s `access_admin`.
- `permission:view_member_section`: Přístup do členské zóny.

### 22.5 Správa v profilu
Členové si mohou ve svém profilu sami spravovat:
- Změnu hesla (vyžaduje potvrzení stávajícím heslem).
- Aktivaci/deaktivaci 2FA.
- Zobrazení a regeneraci záchranných kódů.

## 23. Vizuální sjednocení administrace (Branding UX)
Účel: Zajištění hypermoderního a vizuálně konzistentního zážitku i v administrátorském rozhraní.

### 23.1 Design systému panelu
- **Barvy:** Panel automaticky přebírá barvy z `BrandingService`. Primární barva (tlačítka, aktivní prvky) odpovídá nastavené klubové červené.
- **Loga a favicony:** Administrace využívá nahraná loga z brandingu pro branding panelu a faviconu.

### 23.2 Hypermoderní Login stránka
- **Vlastní Login třída:** `App\Filament\Pages\Auth\Login` zajišťuje vtipné a motivační nadpisy v basketbalovém stylu.
- **Vlastní CSS:** `resources/css/filament-auth.css` přidává:
    - Dynamické gradientní pozadí s brandovými barvami (Navy/Red/Blue).
    - Backdrop blur efekty a jemnou texturu šumu.
    - Specifické stylování karet a tlačítek s důrazem na typografii.
- **Render Hooky:** Do login formuláře jsou vloženy motivační hlášky ("Taktika je připravena...") pro odlehčení UX.

### 23.3 Obnova přístupu
- **Zapomenuté heslo:** V panelu je aktivován odkaz na reset hesla, který využívá sjednocené moderní šablony systému.

### 23.4 CLI a vývoj
```bash
# Aktualizace oprávnění a rolí
php artisan db:seed --class=RoleSeeder

# Pročištění cache po změnách v auth middleware
php artisan optimize:clear
```

### 22.7 Doporučené commity
1) `feat(auth): disable public registration and implement mandatory 2FA for admins`
2) `feat(auth): add invitation system and onboarding tracking`
3) `feat(ui): implement modern login, password reset and 2FA challenge templates`
4) `feat(security): add audit logging for auth events and security logger`
