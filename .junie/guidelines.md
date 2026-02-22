# Junie Guidelines - Projekt Kbelští sokoli

Tento dokument definuje standardy, postupy a strategii pro vývoj projektu Kbelští sokoli. Projekt je postaven na frameworku **Laravel 12** s využitím **Filament PHP 5** pro administraci.

## 1. Jazyk projektu a lokalizace
- **Kód a komentáře:** Kód (názvy tříd, metod, proměnných) píšeme v **angličtině** (standard Laravelu). Komentáře v kódu, pokud jsou nezbytné, píšeme v **češtině**.
- **Uživatelské rozhraní (Bilingvnost):** Celé UI (frontend i backend) je plně **dvojjazyčné** (čeština a angličtina).
    - Výchozím jazykem je čeština (`cs`).
    - Druhým podporovaným jazykem je angličtina (`en`).
- **Lokalizace obsahu (Modely):** Pro překlady polí v databázi používáme balíček `spatie/laravel-translatable`. Překládaná pole jsou v databázi typu `json`.
- **Lokalizace UI:** Používáme standardní Laravel překlady (`lang/*.json` nebo `lang/{locale}/*.php`).
- **Administrace (Filament):** Používáme plugin pro přepínání jazyků a vestavěnou podporu pro translatable atributy ve formulářích a tabulkách.
- **Dokumentace:** Veškerá dokumentace v `docs/` a v tomto souboru je v **češtině**.
- **Databáze:** Názvy tabulek a sloupců v databázi píšeme v **angličtině**. Sloupce s překlady pojmenováváme v jednotném čísle (např. `title`, nikoliv `titles`), protože balíček se o zbytek postará.

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
- Ikony: Používáme **Font Awesome 7 Pro**. Ikony vkládáme pomocí `<i>` tagů s příslušnými třídami ve stylu **Light** (např. `<i class="fa-light fa-basketball"></i>` nebo `<i class="fa-duotone fa-light fa-basketball"></i>`). V celém projektu striktně dodržujeme variantu **Light**.
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

## 8. Správa assetů (Vite, CSS, JS)
- **Konfigurace:** Všechny nové vstupní body (entrypoints) musí být přidány do `vite.config.js`.
- **Build (Kritické):** Po přidání nového souboru do `vite.config.js` nebo při nasazení je nutné spustit `npm run build`, aby se aktualizoval manifest. Bez aktualizace manifestu aplikace vyhodí `ViteException`.
- **Administrace (Filament):** Vlastní assety do administrace vkládáme robustně přes `renderHook('panels::head.end')` v `AdminPanelProvider.php` s využitím direktivy `@vite`.
- **Minifikace:** V administraci nepoužíváme agresivní HTML minifikaci, která by mohla poškodit funkčnost Livewire/Filamentu.

## 9. Konfigurace prostředí
- Používáme specifické proměnné prostředí pro cesty a disky (`PUBLIC_FOLDER`, `UPLOADS_DIR` atd.) pro kompatibilitu s Webglobe hostingem.
- Vždy udržujeme `.env.example` aktuální se všemi klíči (bez citlivých dat).
- Pro integraci s AI využíváme `OPENAI_*` proměnné.

## 10. Artisan generátory a CLI příkazy (Non-interactive Workflow)
Tato sekce definuje povinný postup pro používání generátorů (Laravel, Filament), aby se předešlo zablokování terminálu.

### 10.1 Základní pravidla
- **Non-interactive first (povinné):** Vždy preferujte neinteraktivní příkazy. Používejte příznak `--no-interaction` nebo `-n` jako výchozí pro Artisan a Filament příkazy.
- **Strategie "Generuj minimálně, doplň v kódu":** Pokud generátor vyžaduje dodatečné informace, spusťte nejbezpečnější neinteraktivní verzi (s výchozími hodnotami) a následně kód upravte ručně v PHP třídách. Nepoužívejte interaktivní wizardy.
- **Zákaz blokujících řetězců:** Nepoužívejte dlouhé řetězce příkazů (`cmd1 && cmd2 && ...`), pokud hrozí interaktivní dotaz. Spouštějte příkazy po jednom nebo v bezpečné, ověřené neinteraktivní sekvenci.
- **Bezpečnost terminálu:** Nikdy nenechávejte terminál čekat na odpověď. Pokud se příkaz neočekávaně stane interaktivním, okamžitě proces ukončete a zvolte neinteraktivní variantu nebo manuální editaci kódu.

### 10.2 Filament specifika
- **Resources & Relation Managers:** Pro `make:filament-resource` a `make:filament-relation-manager` vždy používejte `--no-interaction`.
- **Atributy a Schémata:** Pokud není znám atribut pro titulek (`recordTitleAttribute`), nechte jej prázdný a nastavte jej později přímo v kódu. Schémata formulářů a tabulek (`form()`, `table()`) doplňujte ručně po vygenerování.
- **Registrace:** Vztahy (Relation Managers) registrujte v metodě `getRelations()` ručně, pokud nebyly přidány automaticky.

### 10.3 Postup (Pre-flight & Post-generation)
- **Před generováním:** Ověřte názvy modelů a cílových resources. Identifikujte atributy (např. `name`, `title`) pro neinteraktivní parametry.
- **Po generování:** Proaktivně doplňte chybějící části (labels, registrace managerů, úprava schémat, autorizační vazby/Policies, vyčištění placeholderů).

### 10.4 Dokumentace úkolu
Na konci každého úkolu, kde byly použity generátory, musí Junie uvést:
- Přesné použité příkazy (včetně `--no-interaction`).
- Co bylo v kódu doplněno manuálně po generování.
- Případná omezení generátoru.

### 10.5 Příklady (Best Practices)
- **Laravel Model:** `php artisan make:model Product -mf --no-interaction`
- **Filament Resource:** `php artisan make:filament-resource Product --generate --no-interaction`
- **Filament Relation Manager:** `php artisan make:filament-relation-manager CategoryResource products name --no-interaction`
- **Nápověda:** Vždy používejte `--help` k ověření dostupných parametrů před spuštěním.

## 11. Custom Auth UI a Assety (Tailwind v4)
Tato sekce definuje kritická pravidla pro úpravy přihlašovacích stránek a práci s assety v Tailwind v4, aby se předešlo problémům s renderováním.

### 11.1 Tailwind v4 a CSS Entrypointy
- **Explicitní importy:** Každý nový CSS entrypoint (např. `filament-auth.css`) **musí** obsahovat `@import "tailwindcss";` na začátku souboru. Bez toho nebudou Tailwind utility fungovat.
- **Source direktivy:** V CSS souboru musí být definovány `@source` cesty k Blade souborům, které tyto utility používají (např. `@source '../**/*.blade.php';`).

### 11.2 Registrace Assetů v Filamentu
- **Render Hooky:** Assety pro administraci (zejména pro auth stránky) registrujte přímo v `AdminPanelProvider.php` v metodě `panel()` pomocí `->renderHook('panels::head.end', ...)`.
- **Vite direktiva:** Pro vkládání assetů v hooku používejte `@vite(['resources/css/...', 'resources/js/...'])` zabalené do `Blade::render()`.

### 11.3 Stabilita Auth Layoutu
- **Custom Layout:** Pro vlastní vzhled auth stránek používejte dedikovaný layout (např. `resources/views/filament/admin/layouts/auth.blade.php`).
- **Přiřazení:** V PHP třídě stránky (např. `App\Filament\Pages\Auth\Login`) explicitně definujte `protected static string $layout = 'filament.admin.layouts.auth';`.
- **Vyhýbejte se double-wrappingu:** Pokud je shell (gradient, karta) součástí layoutu, nepoužívejte jej znovu jako komponentu uvnitř `$view`.

### 11.4 Verifikace (Snapshoty)
- **Vždy testujte výsledné HTML:** Pokud se změny neprojevují, vygenerujte statický snapshot stránky pomocí `curl` (viz `docs/18-renderovani-html.md`) a zkontrolujte, zda jsou přítomny správné CSS třídy a linky na assety s hashem.
- **Manifest:** Po každé změně v CSS/JS spusťte `npm run build`, aby se aktualizoval Vite manifest.
