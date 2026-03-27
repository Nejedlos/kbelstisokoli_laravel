# Statické nálezy a zranitelnosti (Static Code Analysis)

## 1. XSS v Feedback Snapshotu [HIGH]
- **Soubor:** `resources/views/feedback/snapshot.blade.php` (řádek 39)
- **Nález:** `{!! $dom !!}` renderuje neošetřený HTML kód, který byl nahrán uživatelem přes `/feedback` endpoint.
- **Dopad:** Přihlášený útočník (nebo kdokoli přes CSRF) může odeslat feedback se škodlivým `<script>` payloadem. Pokud admin navštíví `/feedback/snapshot/{token}`, skript se spustí v jeho kontextu (admin session). To může vést ke krádeži session nebo neoprávněným akcím v adminu.
- **Ověření:** Statická analýza `FeedbackController::snapshot` a view `snapshot.blade.php`.
- **Doporučení:** 
  1. Použít knihovnu pro sanitizaci HTML (např. `mews/purifier`) před uložením DOM snapshotu.
  2. V ideálním případě nepoužívat `{!! !!}` pro `$dom`, ale renderovat jej v `sandbox` iframe nebo omezit Content Security Policy (CSP).

## 2. Neautorizovaná Impersonifikace v Screenshot Proxy [HIGH]
- **Soubor:** `app/Http/Controllers/ScreenshotRenderController.php` (řádek 45)
- **Nález:** `Auth::loginUsingId($userId)` je voláno na základě parametru `user_id` v URL, pokud je přítomna platná signatura.
- **Dopad:** Pokud útočník získá `APP_KEY` (např. únik .env), může vygenerovat platnou signaturu pro `/system/screenshot/render?user_id=1&...` a přihlásit se jako admin bez 2FA a bez hesla. I bez `APP_KEY` je tento mechanismus vysoce rizikový, protože signatury mají platnost 5 minut a mohou být zachyceny v logách (např. na NAS serveru).
- **Ověření:** Statická analýza `ScreenshotRenderController::render`.
- **Doporučení:** 
  1. Nepoužívat `Auth::loginUsingId` pro renderování screenshotů. Místo toho předávat data uživatele (např. profilovou fotku, jméno) jako parametry do view v "screenshot mode".
  2. Pokud je impersonifikace nezbytná, omezit ji na konkrétní session driver (např. in-memory) a jen pro daný request.

## 3. SSRF v Screenshot Proxy [MEDIUM]
- **Soubor:** `app/Http/Controllers/ScreenshotRenderController.php` (řádek 83)
- **Nález:** Metoda `isInternalUrl` kontroluje pouze hostitele. Relativní cesty jsou automaticky povoleny.
- **Dopad:** Útočník může zkusit předat URL jako `//127.0.0.1/admin` nebo jiné interní IP adresy. Ačkoliv je tam kontrola signatury, pokud útočník signaturu má (nebo ji nepotřebuje pro určité cesty), může donutit server provést request na interní služby.
- **Ověření:** Statická analýza `ScreenshotRenderController::isInternalUrl`.
- **Doporučení:** Striktněji validovat cílovou URL. Povolit pouze absolutní cesty začínající na `config('app.url')`.

## 4. CSRF Bypass na Feedbacku [MEDIUM]
- **Soubor:** `bootstrap/app.php` (řádek 162)
- **Nález:** Výjimka pro `/feedback` v `validateCsrfTokens`.
- **Dopad:** Útočník může ze svého webu odeslat feedback pod identitou přihlášeného uživatele (CSRF). To může sloužit k zahlcení admina spamem nebo jako vektor pro výše zmíněný XSS útok (seeding XSS do adminu).
- **Ověření:** Kontrola `bootstrap/app.php`.
- **Doporučení:** Odstranit výjimku. Feedback widget v aplikaci by měl používat standardní CSRF token, protože uživatel je již přihlášen. Pokud je widget externí, použít jiný typ validace.

## 5. Slabé heslo k produkční databázi [INFO/ZMIÍRNĚNO]
- **Soubor:** `.env.production` (lokálně)
- **Nález:** V lokálním `.env.production` bylo identifikováno slabší heslo.
- **Dopad:** Protože soubor již není na produkčním serveru, riziko jeho přímého úniku z filesystemu kleslo na minimum. Přesto je vhodné heslo změnit.
- **Ověření:** Statická analýza lokálního souboru.
- **Doporučení:** Změnit heslo v DB i v lokálním .env na náhodný řetězec.

## 6. Expozice GitHub PAT v konfiguraci [INFO/ZMIÍRNĚNO]
- **Soubor:** `.env.production` (lokálně)
- **Nález:** `PROD_GIT_TOKEN` uložen v lokálním environmentu.
- **Dopad:** Soubor byl odstraněn z produkce, čímž se eliminovalo riziko kompromitace GitHubu při úniku souboru ze serveru.
- **Doporučení:** Používat SSH klíče pro deploy nebo aspoň omezit oprávnění PAT tokenu.
