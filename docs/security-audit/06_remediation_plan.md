# Plán oprav (Remediation Plan)

Tento dokument navrhuje konkrétní opravy pro zjištěné zranitelnosti.

## 1. Oprava XSS v Feedback Snapshotu
- **Změna:** Použít jednoduchou sanitizaci pro odstranění `<script>` tagů z `$dom` před renderováním.
- **Kód:** 
```php
// V resources/views/feedback/snapshot.blade.php
{!! preg_replace('/<script\b[^>]*>([\s\S]*?)<\/script>/i', '', $dom) !!}
```
- **Lepší řešení:** Implementovat `mews/purifier` a sanitizovat DOM před uložením do Cache/Storage v `FeedbackController`.

## 2. Zabezpečení Screenshot Proxy (SSRF a Auth)
- **Změna 1 (SSRF):** Upravit `isInternalUrl`, aby akceptovala pouze absolutní cesty začínající na `APP_URL`.
- **Změna 2 (Auth):** Přidat auditní logování každého přihlášení přes screenshot proxy.
- **Kód (ScreenshotRenderController):**
```php
protected function isInternalUrl(string $url): bool
{
    $appUrl = config('app.url');
    // Povolit pouze pokud URL začíná na APP_URL
    return Str::startsWith($url, $appUrl) || Str::startsWith($url, '/');
}
```

## 3. Obnova CSRF ochrany pro Feedback
- **Změna:** Odstranit `/feedback` z výjimek v `bootstrap/app.php`.
- **Důvod:** Feedback widget v aplikaci běží v kontextu session a má přístup k CSRF tokenu. Odstraněním výjimky zabráníme CSRF útokům a spamu.

## 4. Správa .env a GitHub PAT
- **Status:** Soubor `.env.production` byl smazán z produkčního serveru a je spravován pouze lokálně, což eliminuje riziko jeho přímého úniku ze serveru.
- **Doporučení:** 
  - Nadále používat `.env.production` pouze lokálně pro generování konfigurace nebo jako zálohu.
  - Na serveru používat minimální `.env` soubor s nezbytnými údaji.
  - Pokud musí být PAT na serveru, zajistit správná oprávnění (600).

## 5. Zpevnění hesel
- **Doporučení:** Vygenerovat nové, silné heslo pro produkční databázi a aktualizovat lokální `.env.production` i databázi.
