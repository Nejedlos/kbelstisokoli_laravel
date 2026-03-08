# Custom Filament Auth UI (Kbelští sokoli Arena)

Tento modul nahrazuje výchozí Filament auth stránky (Login, Reset Password atd.) plně vlastním UI sjednoceným s designem stránek v přípravě.

## Architektura (Stabilní stav)

Od verze únor 2026 je řešení postaveno na vlastním layoutu panelu, což zajišťuje maximální stabilitu a konzistenci bez nutnosti "double-wrappingu" v každém view.

### Klíčové soubory

- **PHP Třídy (Logic):**
    - `app/Filament/Pages/Auth/Login.php`: Rozšiřuje `Filament\Auth\Pages\Login`. Nastavuje `$layout` na `filament.admin.layouts.auth`.
    - `app/Filament/Pages/Auth/RequestPasswordReset.php`: Rozšiřuje `Filament\Auth\Pages\PasswordReset\RequestPasswordReset`.
    - `app/Filament/Pages/Auth/ResetPassword.php`: Rozšiřuje `Filament\Auth\Pages\PasswordReset\ResetPassword`.

- **Blade Views (UI):**
    - `resources/views/filament/admin/layouts/auth.blade.php`: **Hlavní layout.** Obsahuje arena gradienty, dekorativní objekty, skleněnou kartu a fixní language switcher.
    - `resources/views/filament/admin/auth/login.blade.php`: Obsahuje pouze `{{ $this->form }}`. Shell je dodáván layoutem.

- **Styly a JS:**
    - `resources/css/filament-auth.css`: Všechny designové overridy, animace a utility Tailwind v4.
    - `resources/js/filament-auth.js`: Mikrointerakce (Caps Lock indikátor, shake efekt při chybě, loading stav tlačítka).

## Design Systém (Modern Boxed Style)
- **Pozadí:** Světlejší navy gradient se zářemi (brand red/blue) a plovoucími objekty.
- **Karta (Box):** Solidní bílý box (`#ffffff`) s výraznou horní brandovou linkou (6px) a hlubokými stíny (`shadow-2xl`).
- **Typografie:**
    - Nadpisy: `Oswald` (Italic, 900, uppercase).
    - Texty: `Instrument Sans`.
- **Akcent:** Brandová červená (`--brand-red`) a modrá (`--brand-blue`) pro focus stavy.

## Troubleshooting a časté chyby

### 1. Změny se neprojevují v HTML
**Příčina:** Špatně zaregistrovaný render hook nebo chybějící `@import "tailwindcss"` v entrypointu.
**Řešení:**
- Ověřte `app/Providers/Filament/AdminPanelProvider.php`, zda je hook registrován přes `->renderHook('panels::head.end', ...)`.
- Zkontrolujte, zda `filament-auth.css` obsahuje `@import "tailwindcss";` a `@source` direktivy.

### 2. Formulář je přes celou šířku obrazovky
**Příčina:** Tailwind utility nebyly zkompilovány (chybějící `@import`) nebo jsou přebity výchozím stylem Filamentu.
**Řešení:** V `filament-auth.css` použijte třídu `.ks-auth-container` s `max-width: 32rem !important;`.

### 3. Jazykový přepínač je duplicitní
**Příčina:** Plugin pro lokalizaci vkládá vlastní přepínač.
**Řešení:** Původní přepínač je skryt v `filament-auth.css` pomocí `.fls-display-on { display: none !important; }`. Náš custom přepínač je fixně umístěn v pravém horním rohu v layoutu.

## Verifikace
Pro ověření aktuálního stavu vyrenderovaného HTML použijte příkaz:
```bash
npm run build
curl -sSL -H "Accept: text/html" https://kbelstisokoli.test/admin/login -o login.html
```
Následně zkontrolujte `login.html`, zda obsahuje očekávané třídy (`ks-auth-page`, `ks-auth-container`, `glass-card`).
