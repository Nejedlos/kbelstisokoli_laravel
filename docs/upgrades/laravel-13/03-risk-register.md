# Upgrade Risk Register - Kbelští sokoli (Laravel 13)

Tento dokument mapuje rizika spojená s upgradem frameworku a definuje mitigaci.

## 📋 Přehled rizik

| Riziko | Dopad | Pravd. | Mitigace | Ověření |
| :--- | :--- | :--- | :--- | :--- |
| **Změna bootstrap flow** | Systém nenastartuje, špatné cesty k souborům. | Vysoká | Precizní merge `bootstrap/app.php` po částech. | Spuštění `php artisan` a kontrola logů. |
| **Breaking changes v Spatie balíčcích** | Nefunkční média nebo oprávnění. | Střední | Upgradovat Spatie balíčky samostatně před frameworkem. | Běh `Feature/MediaDownloadTest` a `Feature/PermissionMiddlewareTest`. |
| **Zneplatnění cache a sessions** | Odhlášení všech uživatelů, chyba 419. | Vysoká | Vyčistit cache (`optimize:clear`) ihned po nasazení. | Manuální test login/logout v administraci. |
| **Regrese v AI Normalizeru** | Špatná data ze statistických importů. | Střední | Zmrazení verzí Guzzle/Http a ověření JSON schémat. | Běh `Feature/Stats/Extractors` testů. |
| **Nekompatibilita Filament pluginů** | Rozbité UI administrace (přepínač jazyků). | Střední | Ověřit repozitáře pluginů na L13 podporu, případně dočasně vypnout. | Vizuální kontrola admin panelu a `AdminSmokeTest`. |
| **Změny v Livewire 4 hydration** | Nečekané chování Volt komponent. | Nízká | Kontrola `resources/views/livewire` a testování interaktivity. | Běh `Feature/Livewire/Member/PaymentWidgetTest`. |
| **Změna chování Vite 6/7** | Nefunkční assety (manifest mismatch). | Nízká | Smazat `public/build` a provést čerstvý `npm run build`. | Kontrola konzole prohlížeče na 404 chyby u CSS/JS. |

## 🧪 Verifikační plán (Po upgradu)

### 1. Prvotní kontrola (Smoke Tests)
- `php artisan about` (Ověření verzí)
- `php artisan route:list` (Ověření routingu)
- `php artisan filament:upgrade` (Ověření assetů administrace)

### 2. Automatizované testy
- Spustit kompletní sadu: `php artisan test`
- Prioritní oblasti:
  - Auth (2FA, Impersonation)
  - AI (Search, Stats Normalization)
  - Media (Download, Upload)

### 3. Manuální QA
- Test AI vyhledávání v adminu (včetně sémantiky).
- Test importu statistik z externího zdroje (OpenAI Normalizer).
- Kontrola bilingvního přepínání v administraci i na webu.

## 🛡️ Rollback Strategie
1. **Git:** V případě kritického selhání se vrátit na větev `main` (poslední stabilní commit).
2. **Database:** Před upgradem provést kompletní dump databáze (`sqlite` soubor nebo SQL dump).
3. **Environment:** Zálohovat aktuální `.env` soubor.
