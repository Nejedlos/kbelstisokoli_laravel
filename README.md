# Kbelští sokoli | Informační systém

Vítejte v technickém srdci basketbalového klubu **Kbelští sokoli**. Tento repozitář obsahuje kompletní řešení pro moderní webovou prezentaci a robustní administrativní systém.

## 🏀 O projektu
Systém je navržen tak, aby pokrýval veškeré potřeby sportovního klubu: od správy členské základny, přes organizaci tréninků a zápasů, až po ekonomické řízení a automatizaci marketingových výstupů.

- **Technologie:** Laravel 12 (PHP 8.4+), Filament PHP 5, Livewire, Tailwind CSS v4.
- **Hlavní pilíře:**
    - **Sportovní agenda:** Zápasy, tréninky, klubové akce a soutěže.
    - **Členská sekce:** Dashboardy pro hráče a trenéry, branding člena.
    - **Ekonomika:** Automatizovaná správa plateb, propojení s bankou (přes externí moduly).
    - **Obsah a média:** CMS pro web, fotogalerie, automatické generování grafiky.
    - **AI integrace:** Inteligentní vyhledávání, predikce výsledků, asistent pro trenéry.

## 🛠️ Architektura systému
Systém využívá moderní Laravel patterny pro zajištění stability a rozšiřitelnosti:
- **Folio & Volt:** Pro bleskově rychlý a interaktivní frontend.
- **Filament PHP:** Nejpokročilejší administrace v PHP ekosystému, plně lokalizovaná (CS/EN).
- **Spatie MediaLibrary:** Robustní správa fotografií a dokumentů.
- **Sanctum & Fortify:** Bezpečná autentizace a správa uživatelů včetně 2FA.

## 🚀 Rychlý start pro vývojáře

1. **Prerekvizity:** PHP 8.4, Node.js 22+, SQLite/MySQL.
2. **Instalace:**
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate --seed
   npm run dev
   ```
3. **Přihlášení:** Výchozí admin je `admin@kbelstisokoli.cz` (pokud byl spuštěn seeder).

## 📖 Dokumentace
Veškeré detaily k vývoji, provozu a správě naleznete v adresáři `docs/cs/`. Tato dokumentace je rovněž přístupná přímo v administraci systému v sekci **Dokumentace**.

- [**Struktura projektu**](docs/cs/01-general/03-struktura-projektu.md)
- [**Vývojové standardy**](docs/cs/02-development/01-sprava-assetu.md)
- [**Nasazení na produkci**](docs/cs/06-ops/01-nasazeni.md)

---
© 2026 Kbelští sokoli. Vyvinuto s ❤️ pro basketbal.
