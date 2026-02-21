# Struktura projektu Kbelští sokoli

## Hlavní komponenty
Projekt využívá následující technologický stack:

- **Framework:** Laravel 12.x (vyžaduje PHP 8.4+)
- **Backend administrace:** Filament PHP 5.x
- **Routing:** Laravel Folio (pro frontend i backend)
- **Frontend:** Blade, Livewire 3.x, Tailwind CSS, Vite
- **Autentizace:** Laravel Fortify, Laravel Sanctum
- **Oprávnění:** Spatie Laravel Permission
- **Správa souborů:** Spatie Laravel Media Library
- **Databáze:** SQLite (lokální vývoj), MySQL/PostgreSQL (produkce)

## Adresářová struktura
- `app/Filament/` - Obsahuje definice Filament Resource a Pages pro administraci.
- `app/Models/` - Databázové modely (Eloquence).
- `resources/views/` - Blade šablony pro frontend.
- `resources/views/pages/` - Folio stránky (routing založený na souborech).
- `database/migrations/` - Migrace databázového schématu.
- `docs/` - Tato technická dokumentace.
- `.github/workflows/` - CI/CD konfigurace pro GitHub.
- `Envoy.blade.php` - Skript pro SSH deployment.
- `.junie/` - Instrukce a guidelines pro AI asistenta Junie.
