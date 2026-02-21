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
- **Přiřazení (výchozí skeleton):**
  - `admin`: všechna oprávnění
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

### Technické řešení
- **Datový model:**
    - `page_blocks`: tabulka pro ukládání bloků (vazba na `page_id`, připravena pro budoucí polymorfní použití).
    - `pages.content`: JSON sloupec využívaný pro přímou integraci s Filament Builderem.
- **JS Knihovny:**
    - `SortableJS`: zajišťuje drag & drop řazení bloků (integrováno ve Filamentu).
    - `Tiptap`: moderní core pro rich-text editor (připraveno pro budoucí hlubší integraci, aktuálně využit standardní RichEditor).
- **Centrální registr:** `App\Services\Cms\BlockRegistry` definuje schémata a ikony pro všech 11 typů bloků.

### Podporované typy bloků
1. **Hero sekce:** nadpis, podnadpis, CTA, obrázek na pozadí, zarovnání.
2. **Textový blok:** formátovaný text.
3. **Obrázek:** s popiskem a alt textem.
4. **CTA:** výrazná výzva k akci s volitelným stylem.
5. **Mřížka karet:** opakovatelné položky s ikonou, titulkem a popisem.
6. **Statistické karty:** číselné údaje pro sportovní/ekonomické výstupy.
7. **Výpis novinek:** napojení na moduly novinek (limit, kategorie).
8. **Výpis zápasů:** napojení na moduly zápasů (nadcházející/výsledky).
9. **Galerie:** mřížka obrázků s reorderem.
10. **Kontakt / Info:** adresa, kontakty, volitelná mapa.
11. **Vlastní HTML:** pro embed kódy a pokročilé úpravy (raw HTML).

### Renderování (Frontend)
- Komponenta `<x-page-blocks :blocks="$page->content" />` prochází pole bloků a inkluduje příslušné Blade šablony z `resources/views/public/blocks/`.
- Každá šablona má přístup k proměnné `$data` obsahující payload bloku.

### Jak přidat nový typ bloku
1. Přidejte metodu a registraci v `App\Services\Cms\BlockRegistry`.
2. Vytvořte Blade šablonu v `resources/views/public/blocks/{slug}.blade.php`.
3. Spusťte `npm run build` (pokud měníte styly).
