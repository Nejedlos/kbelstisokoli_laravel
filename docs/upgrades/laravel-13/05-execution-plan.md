# Laravel 13 Execution Plan - Kbelští sokoli

Tento dokument definuje přesný postup upgradu projektu z Laravel 12 na Laravel 13, včetně migrace AI vrstvy na nativní SDK.

## 📋 Fáze A: Prerequisite a Cleanup
Cíl: Příprava čistého a stabilního prostředí před zahájením samotného upgradu.

| Krok | Účel | Příkaz | Bezpečnost | Ověření |
| :--- | :--- | :--- | :--- | :--- |
| A1 | Formátování kódu | `php artisan pint` | **safe** | `git diff` |
| A2 | Povýšení Spatie balíčků | `composer update spatie/laravel-medialibrary spatie/laravel-permission --with-all-dependencies` | **caution** | `php artisan test` |
| A3 | Vyčištění cache | `php artisan optimize:clear` | **safe** | Bez chyb v terminálu |
| A4 | Stabilizace testů | `php artisan test` | **safe** | Všechny testy (vč. Dusk) PASS |

## 📦 Fáze B: Composer Dependency Alignment
Cíl: Aktualizace verzí v `composer.json` a instalace závislostí pro L13.

| Krok | Účel | Příkaz | Bezpečnost | Ověření |
| :--- | :--- | :--- | :--- | :--- |
| B1 | Update Laravel Core | `composer require laravel/framework:^13.0 --no-update` | **safe** | `composer.json` změna |
| B2 | Update AI SDK | `composer require laravel/ai-sdk:^1.0 --no-update` (odhad) | **safe** | `composer.json` změna |
| B3 | Zarovnání ostatních | `composer update --no-scripts` | **caution** | `composer.lock` update |
| B4 | Detekce konfliktů | Ruční kontrola `composer.json` vs `composer.lock` | **safe** | Žádné errory při `composer install` |

## 🚀 Fáze C: Laravel 13 Core Upgrade
Cíl: Přizpůsobení bootstrapu a konfigurací novému skeletonu.

| Krok | Účel | Příkaz | Bezpečnost | Ověření |
| :--- | :--- | :--- | :--- | :--- |
| C1 | Merge bootstrap/app.php | Ruční merge změn z L13 skeletonu (zejména `withRouting`) | **potentially destructive** | `php artisan about` |
| C2 | Update config/* | `php artisan config:publish --all` (nebo ruční merge) | **caution** | Srovnání s `.env` |
| C3 | Middleware Alignment | Kontrola pořadí v `bootstrap/app.php` | **caution** | Test login flow |

## 🎨 Fáze D: Livewire / Filament Upgrade
Cíl: Zajištění funkčnosti administrace a interaktivních prvků.

| Krok | Účel | Příkaz | Bezpečnost | Ověření |
| :--- | :--- | :--- | :--- | :--- |
| D1 | Update Filament | `composer require filament/filament:^6.0` (odhad) | **caution** | `php artisan filament:upgrade` |
| D2 | Asset Refresh | `npm install && npm run build` | **safe** | Kontrola `manifest.json` |
| D3 | UI Smoke Test | Proklikání Filament administrace | **safe** | Žádné JS errory v konzoli |

## 🤖 Fáze E: AI Migration to Laravel-native SDK
Cíl: Postupné nasazení nové AI architektury dle Design dokumentu.

| Krok | Účel | Příkaz | Bezpečnost | Ověření |
| :--- | :--- | :--- | :--- | :--- |
| E1 | SDK Infrastructure | Konfigurace AI providerů v `AppServiceProvider` | **safe** | `Ai::generate()` test |
| E2 | Structured Output | Migrace `AiTextEnhancer` na `NormalizeStatsAction` | **caution** | `Feature/Stats/Extractors` test |
| E3 | Vector Migration | Update `ai_chunks` tabulky (vektorový sloupec) | **potentially destructive** | `php artisan ai:reindex` |
| E4 | Agent RAG | Implementace `ClubAssistantAgent` | **safe** | `Ai::fake()` testy |
| E5 | UI Switch | Aktivace `AI_USE_SDK=true` v `.env` | **safe** | Test search v adminu |

## 🛡️ Fáze F: Testing, Fixes, Stabilization
Cíl: Finální verifikace a oprava drobných regresí.

| Krok | Účel | Příkaz | Bezpečnost | Ověření |
| :--- | :--- | :--- | :--- | :--- |
| F1 | Full Test Suite | `php artisan test --parallel` | **safe** | 100% PASS |
| F2 | Log Audit | `tail -f storage/logs/laravel.log` | **safe** | Žádné nové `Deprecated` ani `Fatal` |
| F3 | Cleanup | `git add . && git commit -m "chore: complete laravel 13 upgrade"` | **safe** | Čistý repo stav |

---

## 🔄 Revert & Rollback Strategie

### Rychlý Revert (Git)
V případě selhání v jakémkoliv kroku (zejména po `composer update` nebo `bootstrap/app.php` editaci):
```bash
git reset --hard HEAD
composer install
php artisan optimize:clear
```

### Databázový Rollback
Pokud byly spuštěny destruktivní migrace (Fáze E3):
1. Obnovit DB ze zálohy: `cp storage/database.sqlite.bak database.sqlite`
2. Pokud MySQL/Postgres: `php artisan migrate:rollback --step=X`

### Feature Flag Rollback (AI)
Pokud AI SDK vykazuje halucinace nebo chyby:
1. Nastavit `AI_USE_SDK=false` v `.env`
2. Systém se vrátí k původním `App\Services\Ai*` třídám.
