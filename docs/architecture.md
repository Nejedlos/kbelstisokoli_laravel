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


## 6. CMS jádro (Struktura a správa obsahu)
- Účel: Univerzální backend správa obsahu (stránky, novinky, kategorie, menu) s připravenou SEO vrstvou a publikovacím workflow.

### Datový model
- `post_categories`: název, slug (unikátní), popis, `sort_order`.
- `posts`: `category_id` (nullable), titul, slug (unikátní), `excerpt`, `content` (longtext), `status` (draft/published), `publish_at` (nullable), `featured_image` (string), `is_visible` (bool).
- `pages`: titul, slug (unikátní), `content` (JSON) – připraveno na bloky, `status` (draft/published), `is_visible` (bool).
- `menus`: název, `location` (unikátní, nullable).
- `menu_items`: `menu_id`, `parent_id` (nullable), `label`, `url` (nullable), `route_name` (nullable), `target` (`_self|_blank`), `sort_order`, `is_visible` (bool).
- `seo_metadatas`: polymorfní SEO pro `Page` a `Post` (trait `HasSeo`).

Pozn.: Tagy lze doplnit následně (`tags`, `post_tag` pivot) bez zásahu do jádra.

### Publikační workflow a viditelnost
- `status` (draft/published) + `publish_at` (u příspěvků) definují publikovatelnost.
- `is_visible` umožní rychle dočasně skrýt objekt bez změny obsahu.
- Řazení: `sort_order` u kategorií a položek menu; příspěvky primárně dle `publish_at`/vytvoření.

### Administrace (Filament)
- Resource: `Pages\PageResource`, `Posts\PostResource`, `PostCategories\PostCategoryResource`, `Menus\MenuResource`.
- Formuláře: srozumitelné popisky, helper texty pro slug, přepínače viditelnosti, sekce SEO přes `CmsForms::getSeoSection()`.
- Menu položky: Relation Manager `ItemsRelationManager` s možností řazení (`reorderable('sort_order')`) a parent-child vazbou.

### Rozšiřitelnost
- Blokový obsah: `pages.content` (JSON) připraven pro Page Builder/Blocks (další prompt).
- Média: `featured_image` + SEO `og_image` jsou string reference – snadná pozdější integrace Spatie Media Library.
- Oprávnění: stávající role/permissions (`manage_content`, `access_admin`) – lze připojit k Resource (policies) bez zásahů do schématu.
