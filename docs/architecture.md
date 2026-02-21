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

### 11.11 Doporučené commity
1) feat(users): add is_active/phone/last_login_at/admin_note and enforce active login
2) feat(players): introduce PlayerProfile + team pivot and Filament resources
3) feat(admin): add user/profile policies, role/permission overview, KPI dashboard
4) chore: docs for user management, policies, and dashboard
