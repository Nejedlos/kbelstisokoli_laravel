# Custom Filament Auth UI

Tento modul nahrazuje výchozí Filament auth stránky (Login, Reset Password atd.) plně vlastním UI sjednoceným s designem 2FA stránky.

## Architektura

Řešení je postaveno na rozšíření základních Livewire komponent (Pages) Filamentu a přiřazení vlastních Blade views v `AdminPanelProvider`.

### Soubory

- **PHP Třídy (Logic):**
    - `app/Filament/Pages/Auth/Login.php`: Rozšiřuje `Filament\Auth\Pages\Login`.
    - `app/Filament/Pages/Auth/RequestPasswordReset.php`: Rozšiřuje `Filament\Auth\Pages\PasswordReset\RequestPasswordReset`.
    - `app/Filament/Pages/Auth/ResetPassword.php`: Rozšiřuje `Filament\Auth\Pages\PasswordReset\ResetPassword`.
    - `app/Filament/Pages/Auth/EmailVerificationPrompt.php`: Rozšiřuje `Filament\Auth\Pages\EmailVerification\EmailVerificationPrompt`.
    - `app/Filament/Pages/Auth/Register.php`: Rozšiřuje `Filament\Auth\Pages\Register`.

- **Blade Views (UI):**
    - `resources/views/filament/admin/auth/login.blade.php`
    - `resources/views/filament/admin/auth/request-password-reset.blade.php`
    - `resources/views/filament/admin/auth/reset-password.blade.php`
    - `resources/views/filament/admin/auth/email-verification-prompt.blade.php`
    - `resources/views/filament/admin/auth/register.blade.php`
    - `resources/views/filament/admin/auth/partials/shell.blade.php`: Hlavní layout (wrapper) pro všechny auth stránky.

- **Komponenty:**
    - `resources/views/components/auth-header.blade.php`: Hlavička s ikonou a brandingem.
    - `resources/views/components/auth-footer.blade.php`: Patička s odkazy.

- **Styly:**
    - `resources/css/filament-auth.css`: Obsahuje všechny override pro auth stránky (glass-card, animace, formulářové prvky).

## Jak přidat další auth stránku

1. Vytvořte novou PHP třídu v `app/Filament/Pages/Auth/` rozšiřující příslušnou Filament base page.
2. Nastavte `$view` na vaši novou Blade šablonu.
3. V Blade šabloně použijte `<x-filament-panels::layout.base :livewire="$this">` a vložte `@include('filament.admin.auth.partials.shell', [...])`.
4. Zaregistrujte novou stránku v `app/Providers/Filament/AdminPanelProvider.php` pomocí příslušné metody v `panel()` (např. `->login(...)`, `->registration(...)`).

## Co zkontrolovat po upgradu Filamentu

Při major updatu Filamentu zkontrolujte:
1. Namespace základních tříd, které rozšiřujeme (base classes).
2. Podpisy metod v base classes (zejména `form()` nebo akce, pokud jsme je přepisovali).
3. Názvy Livewire akcí volaných z Blade (např. `wire:submit="authenticate"`).
4. Strukturu `filament-panels::layout.base`, pokud by došlo k velkým změnám v layoutu Filamentu.

## Design Systém
- **Pozadí:** Tmavý gradient s animovanými "floating objects".
- **Karta (Surface):** Glass-card s 85% bílým pozadím (nebo dynamicky dle CSS proměnných), blur efektem a jemným borderem.
- **Akcent:** Brandová barva (výchozí růžovo-červená `#E11D48`) z `BrandingService`.
- **Typografie:** Oswald pro nadpisy, Instrument Sans pro texty.
