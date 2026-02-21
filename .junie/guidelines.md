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

## 9. Konfigurace prostředí
- Používáme specifické proměnné prostředí pro cesty a disky (`PUBLIC_FOLDER`, `UPLOADS_DIR` atd.) pro kompatibilitu s Webglobe hostingem.
- Vždy udržujeme `.env.example` aktuální se všemi klíči (bez citlivých dat).
- Pro integraci s AI využíváme `OPENAI_*` proměnné.

## 10. Artisan generátory a CLI příkazy
- **Preference neinteraktivních příkazů:** Při generování kódu přes Laravel/Filament (např. `make:model`, `make:filament-resource`) preferujeme **plně specifikované příkazy**, aby terminál nečekal na doplňující otázky.
- **Parametry a příznaky:**
    - Pokud je příkaz standardně interaktivní, použijte příznak `--no-interaction` nebo `-n`.
    - Všechny potřebné parametry (názvy modelů, labelů, relací, resources) uvádějte přímo v příkazu.
- **Postup u nevyhnutelně interaktivních příkazů:** Pokud neinteraktivní režim není možný, Junie musí:
    1. Předem uvést, jaké otázky budou v terminálu položeny.
    2. Poskytnout přesné odpovědi (text nebo volbu), které mají být použity.
    3. Vyhnout se řetězení příkazů (batching), pokud by hrozilo zablokování na skrytém dotazu.
- **Filament specifika:** Preferujte předvídatelné vzorce příkazů a vyhněte se komplexním dávkám, pokud nejsou všechny odpovědi předem známé a zdokumentované.
- **Dokumentace příkazů:** Na konci každého úkolu, kde byly použity generátory, stručně zaznamenejte použité příkazy (např. v dokumentaci modulu nebo v popisu úkolu).

### Best practices pro CLI generování (Příklady)
- **Laravel:**
    - Místo `php artisan make:model Product` (které se ptá na migraci/factory) použijte `php artisan make:model Product -mf`.
- **Filament:**
    - Místo `php artisan make:filament-resource Product` (které se ptá na model/soft-deletes/view) použijte `php artisan make:filament-resource Product --model=Product --view --soft-deletes`.
- **Obecné:**
    - Vždy používejte `--help` k ověření dostupných parametrů před spuštěním, abyste se vyhnuli interaktivitě.
