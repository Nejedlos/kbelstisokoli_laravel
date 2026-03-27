# Architektonické nálezy a review konfigurace

## 1. Správa tajemství a .env souborů
- **Zjištění:** Původní soubor `.env.production` obsahoval citlivé údaje včetně `PROD_GIT_TOKEN` (GitHub PAT) a `OPENAI_API_KEY`.
- **Status:** Soubor byl smazán z produkčního serveru a je spravován pouze lokálně (offline), což výrazně snižuje riziko jeho úniku přes webserver.
- **Závažnost:** INFO (Po smazání z produkce).
- **Doporučení:** Nadále spravovat citlivé údaje mimo produkční filesystem.

## 2. Screenshot Proxy a automatické přihlášení
- **Soubor:** `app/Http/Controllers/ScreenshotRenderController.php`
- **Zjištění:** Metoda `render()` používá `Auth::loginUsingId($userId)` pokud je přítomno `user_id` a platná signatura.
- **Riziko:** Pokud útočník dokáže vygenerovat platnou signaturu (např. získá `APP_KEY`), může se přihlásit jako kterýkoliv uživatel bez hesla.
- **Závažnost:** HIGH (Závisí na ochraně `APP_KEY`).
- **Doporučení:** Přihlášení přes `loginUsingId` by mělo být omezeno pouze na velmi krátkou dobu a pouze pro specifické účely renderování. Zvážit použití dočasného tokenu místo `Auth::loginUsingId`.

## 3. Výjimky z CSRF ochrany
- **Soubor:** `bootstrap/app.php`
- **Zjištění:** Endpointy `/feedback` a `*/feedback/*` jsou vyjmuty z CSRF ochrany.
- **Riziko:** CSRF útok může být použit k odeslání falešných hlášení pod jménem uživatele, pokud je uživatel přihlášen.
- **Závažnost:** LOW/MEDIUM (Závisí na tom, co všechno `FeedbackController::store` dělá).
- **Doporučení:** Pokud je feedback určen pro přihlášené uživatele, CSRF ochrana by měla být aktivní. Pokud je pro externí widgety, je potřeba jiný způsob validace (např. origin check nebo API klíč).

## 4. Web-cron a Scheduler Tokeny
- **Soubory:** `routes/web.php`, `routes/public.php`, `app/Http/Controllers/System/CronController.php`
- **Zjištění:** Existují dva endpointy pro spouštění scheduleru přes HTTP. Jeden používá `config('system.cron.token')` a druhý `config('app.schedule_token')`.
- **Riziko:** Pokud jsou tokeny slabé nebo uniklé, útočník může spouštět plánované úlohy, což může vést k zátěži systému nebo spuštění citlivých procesů.
- **Závažnost:** LOW.
- **Doporučení:** Sjednotit mechanismus a zajistit rotaci tokenů.

## 5. Impersonifikace
- **Soubor:** `app/Http/Controllers/Admin/ImpersonateController.php`
- **Zjištění:** Logika impersonifikace zneplatňuje session a regeneruje token, což je správné. Používá ale `Auth::login($userToImpersonate)`, což může obcházet 2FA cílového uživatele (i když admin už 2FA prošel).
- **Riziko:** Admin může přistupovat k účtům bez vědomí uživatele (by design), ale je důležité, aby auditní logy toto chování zaznamenávaly.
- **Závažnost:** INFO.
- **Doporučení:** Ujistit se, že každá impersonifikace je zaznamenána v auditu (trait `Auditable` je na modelu User přítomen).
