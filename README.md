# Kbelští sokoli

Projekt moderní webové prezentace a administrativního systému pro klub **Kbelští sokoli**. Systém je postaven na frameworku **Laravel 12** a využívá **Filament PHP 5** pro správu dat a administraci.

## Hlavní vlastnosti
- **Moderní UI:** Postaveno na Blade, Livewire a Tailwind CSS s využitím Laravel Folio pro routing.
- **Komplexní Administrace:** Správa uživatelů, ekonomický modul a klubové záležitosti přes Filament.
- **Automatizovaný Deployment:** Podpora pro Laravel Envoy a CI/CD přes GitHub Actions.
- **Lokalizace:** Celé uživatelské rozhraní i dokumentace jsou v češtině.

## Technický Stack
- **PHP:** ^8.4
- **Framework:** Laravel 12.x
- **Administrace:** Filament PHP 5.x
- **Databáze:** SQLite (lokálně) / MySQL (produkce)
- **Deployment:** GitHub + SSH (Webglobe) + Laravel Envoy

## Rychlý start (Lokální vývoj)

1. **Klonování repozitáře:**
   ```bash
   git clone https://github.com/Nejedlos/kbelstisokoli_laravel.git
   cd kbelstisokoli_laravel
   ```

2. **Instalace závislostí:**
   ```bash
   composer install
   npm install
   ```

3. **Nastavení prostředí:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Databáze a migrace:**
   ```bash
   php artisan migrate --seed
   ```

5. **Spuštění vývojového serveru:**
   ```bash
   npm run dev
   # v jiném terminálu
   php artisan serve
   ```

## Dokumentace
Podrobná dokumentace k projektu se nachází v adresáři `docs/`:

- [**Index dokumentace**](docs/index.md) - Hlavní rozcestník všech témat.
- [Základní koncepty](docs/01-zakladni-koncepty/01-uvod.md) - Přehled a architektura.
- [Nasazení (Deployment)](docs/07-provoz-a-nasazeni/01-nasazeni.md) - Návod pro produkční server.
- [AI Funkce](docs/05-ai-funkce/01-ai-vyhledavani.md) - Správa a nastavení AI.

## Vývojové pokyny
- Dodržujeme **PSR-12** a používáme **Laravel Pint** pro formátování.
- Veškerý kód je v angličtině, ale UI a komentáře/dokumentace jsou v **češtině**.
- Každá nová funkce musí být zdokumentována v `docs/`.
- Podrobná pravidla naleznete v [.junie/guidelines.md](.junie/guidelines.md).

---
© 2026 Kbelští sokoli. Spravováno pomocí [GitHubu](https://github.com/Nejedlos/kbelstisokoli_laravel).
