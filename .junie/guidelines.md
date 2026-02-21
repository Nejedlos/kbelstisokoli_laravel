# Junie Guidelines - Projekt Kbelští sokoli

Tento dokument definuje standardy, postupy a strategii pro vývoj projektu Kbelští sokoli. Projekt je postaven na frameworku **Laravel 12** s využitím **Filament PHP 5** pro administraci.

## 1. Jazyk projektu
- **Kód a komentáře:** Kód (názvy tříd, metod, proměnných) píšeme v **angličtině** (standard Laravelu). Komentáře v kódu, pokud jsou nezbytné, píšeme v **češtině**.
- **Uživatelské rozhraní:** Celé UI (frontend i backend) je výhradně v **češtině**.
- **Dokumentace:** Veškerá dokumentace v `docs/` a v tomto souboru je v **češtině**.
- **Databáze:** Názvy tabulek a sloupců v databázi píšeme v **angličtině**.

## 2. Vývojová strategie
- **Backend:** Laravel 12 s využitím moderních prvků (Folio pro routing, Sanctum pro API, Fortify pro auth).
- **Administrace:** Filament PHP 5. Všechny administrativní nástroje (User management, Economy management) budou realizovány jako Filament Resource nebo Custom Page.
- **Frontend:** Laravel Folio a Blade/Livewire (podle potřeby interaktivity).
- **Správa médií:** Spatie Laravel Media Library.
- **Oprávnění:** Spatie Laravel Permission.

## 3. Dokumentace (Povinnost)
- Každá nová funkce, modul nebo významná změna **musí** být zaznamenána v adresáři `docs/`.
- Dokumentace by měla obsahovat:
    - Účel modulu.
    - Technický popis (pokud je složitější).
    - Způsob použití (pro adminy/uživatele).
- Dokumentace se píše v Markdown formátu.

## 4. Coding Standardy
- Dodržujeme **PSR-12**.
- Používáme **Laravel Pint** pro formátování kódu (konfigurace v projektu).
- Typování: Vždy definujeme návratové typy metod a typy parametrů, kde je to možné.
- Pojmenování:
    - Modely: Jednotné číslo, PascalCase (např. `Member`).
    - Kontrolery: PascalCase + přípona `Controller` (např. `MemberController`).
    - Migrace: Standardní Laravel formát (snake_case).

## 5. Správa databáze
- Všechny změny schématu provádíme výhradně přes migrace.
- Pro testovací data používáme Seedery a Factory.

## 6. Git a Commit zprávy
- Commit zprávy píšeme v češtině nebo angličtině (dle dohody, preferujeme konzistenci).
- Každý commit by měl být atomický a srozumitelný.

## 7. Strategie nasazení (Deployment)
- **Source Control:** GitHub je jediným zdrojem pravdy pro kód. Na GitHub neukládáme citlivá data (používáme `.env`).
- **Produkční prostředí:** Na produkčním serveru (Webglobe) pracujeme výhradně přes SSH.
- **Workflow:**
    1. Lokální vývoj a push na GitHub.
    2. Na produkci se změny stahují pomocí `git pull` (přes SSH).
    3. Následně se spouští podpůrné příkazy (`composer install`, `npm install`, `npm run build`, `php artisan migrate`).
- **Automatizace:** Pro nasazení používáme **Laravel Envoy** (viz `Envoy.blade.php`).

## 8. Práce s Junie
- Junie bude při každé změně aktualizovat příslušnou část dokumentace v `docs/`.
- Junie bude dodržovat tyto guidelines a v případě nejasností se dotáže.
- Nové poznatky z vývoje budou průběžně doplňovány do tohoto souboru `guidelines.md`.
