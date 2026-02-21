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

### Administrace (Filament)
- Všechny entity jsou dostupné v navigační skupině **Sportovní agenda**.
- Formuláře jsou optimalizovány pro rychlé zadávání (výběr z číselníků, automatické stavy).
- Zápasy mají v přehledu barevně odlišené stavy a výsledky.

### Veřejné zobrazení
- `/zapasy`: Seznam všech zápasů s paginací.
- `/zapasy/{id}`: Detail zápasu s výsledkem a veřejnou poznámkou.
- `/treninky`: Přehled tréninkových informací rozdělený podle týmů.
