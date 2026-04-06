# Laravel 13 Compatibility Audit - Kbelští sokoli

Tento dokument obsahuje detailní analýzu kompatibility projektu před upgradem na **Laravel 13**, **Livewire 4** a **Filament 5**.

## 📦 Aktuální stav a cílové verze

| Balíček | Aktuální verze | Cílová verze (Odhad) | Poznámka |
| :--- | :--- | :--- | :--- |
| **PHP** | 8.4.19 | 8.4+ | Laravel 13 vyžaduje minimálně PHP 8.4. |
| **Laravel Framework** | 12.52.0 | 13.0.0 | Hlavní cíl upgradu. |
| **Livewire** | 4.1.4 | 4.x / 5.x | Projekt již používá Livewire 4. |
| **Filament PHP** | 5.2.2 | 5.x / 6.x | Aktuálně na v5, ověřit stabilitu na L13. |
| **Folio** | 1.1.12 | 2.x? | Ověřit podporu pro L13. |
| **Volt** | 1.10.3 | 2.x? | Ověřit podporu pro L13. |
| **Spatie MediaLibrary** | 11.20.0 | 12.x? | Častý zdroj breaking changes při upgradech. |
| **Spatie Permission** | 7.2.0 | 8.x? | Stabilní, ale vyžaduje kontrolu migrací. |
| **Laravel Scout** | 10.24.0 | 11.x? | Klíčové pro AI vyhledávání. |

## 🛠️ Analýza Service Providerů a Bootstrapu

### Bootstrap (bootstrap/app.php)
- **Riziko:** Vysoké.
- **Zjištění:** Soubor je silně upraven (vlastní `booting` pro cesty, dynamický scheduler z DB, komplexní middleware stack).
- **Akce:** Po upgradu frameworku bude nutné ručně sloučit změny z nového skeletonu L13, zejména v oblasti `withRouting` a `withMiddleware`.

### Service Providery (app/Providers/*)
- **AppServiceProvider:** Obsahuje makra a globální boot logiku.
- **AdminPanelProvider:** Obsahuje komplexní `renderHook` logiku pro vkládání CSS/JS a custom auth flow. Riziko změn v API Filamentu (v5 -> v6?).
- **FortifyServiceProvider:** Vlastní úpravy pro 2FA flow.

## 🚦 Balíčky k prověření (Potenciální blokátory)

| Balíček | Stav | Riziko |
| :--- | :--- | :--- |
| `bezhansalleh/filament-language-switch` | ^4.1 | **Střední**. Ověřit kompatibilitu s L13 a Filament 5/6. |
| `voku/html-min` | ^4.5 | **Nízké**. Používáno v middleware pro minifikaci. |
| `mews/purifier` | ^3.4 | **Nízké**. Čištění HTML vstupu. |
| `propaganistas/laravel-phone` | ^6.0 | **Nízké**. Validace telefonních čísel. |
| `owenvoke/blade-fontawesome` | ^3.1 | **Nízké**. Integrace FA ikon. |

## ⚠️ Interní kód (Breaking Changes)

1. **Eloquent Serialization:** Projekt používá `anourvalar/eloquent-serialize` v `AiSearchService` (nepřímo přes joby?). Ověřit, zda nativní L13 nepřináší změny v serializaci.
2. **FileSystem Cesty:** Custom path logic v `bootstrap/app.php` (pro Webglobe hosting) musí zůstat zachována.
3. **AI Search Custom Scorer:** Heuristický výpočet skóre v `AiIndexService::search` může vyžadovat úpravu, pokud se změní chování Eloquentu/Collections.
4. **Middleware Priorities:** V `bootstrap/app.php` je definována priorita middleware. Nutno ověřit, zda L13 nemění výchozí pořadí (zejména `StartSession` vs `Authenticate`).

## 🔍 Blocking Issues
- Žádné kritické blokátory (hard dependencies na staré verze) nebyly identifikovány v `composer.json`.
- Hlavním "soft" blokátorem je komplexnost `bootstrap/app.php`, která vyžaduje velmi precizní refaktoring.

## ✅ Safe First Changes (Příprava)
1. **Povýšení balíčků Spatie:** Upgradovat na nejnovější minor verze MediaLibrary a Permission ještě před skokem na L13.
2. **Stabilizace testů:** Ujistit se, že všechny testy (včetně Dusk) procházejí na aktuální verzi L12.
3. **Deprecations:** Zapnout hlášení deprecations a opravit všechna varování v logu.
4. **Laravel Pint:** Spustit formátování kódu pro čistý diff.
