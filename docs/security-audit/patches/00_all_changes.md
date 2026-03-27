# Přehled všech bezpečnostních oprav (Patch Set)

Tento dokument obsahuje diffy nejdůležitějších bezpečnostních oprav provedených v rámci auditu.

## 1. Bezpečnostní hlavičky (R1)
**Soubor:** `bootstrap/app.php` a `app/Http/Middleware/SecurityHeadersMiddleware.php`

```php
// bootstrap/app.php
$middleware->web(append: [
    \App\Http\Middleware\SecurityHeadersMiddleware::class,
    // ...
]);

// app/Http/Middleware/SecurityHeadersMiddleware.php
$response->headers->set('X-Frame-Options', 'SAMEORIGIN');
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
```

## 2. Robustní sanitizace Feedback Snapshotu (R2)
**Soubor:** `app/Http/Controllers/FeedbackController.php`

```php
$dom = $data['dom'] ?? '';
// Odstranění všech <script> tagů a event handlerů
$dom = preg_replace('/<script\b[^>]*>([\s\S]*?)<\/script>/i', '', $dom);
$dom = preg_replace('/\s+on\w+="[^"]*"/i', '', $dom);
$dom = preg_replace('/(href|src)\s*=\s*["\']javascript:[^"\']*["\']/i', '$1="#"', $dom);
```

## 3. Autorizace v MediaDownloadController (R3)
**Soubor:** `app/Http/Controllers/MediaDownloadController.php`

```php
// Původně:
if ($model instanceof \App\Models\MediaAsset) {
    $this->authorizeAccess($model);
}

// Nově:
if ($model instanceof \App\Models\MediaAsset) {
    $this->authorizeAccess($model);
} else {
    $this->authorize('view', $model);
}
```

## 4. Sanitizace CMS bloků (R4)
**Soubor:** `app/Support/HtmlSanitizer.php` a Blade šablony

```php
// resources/views/components/public/blocks/rich_text.blade.php
{!! \App\Support\HtmlSanitizer::clean($content ?? '', false) !!}
```

## 5. Zabezpečení Screenshot Proxy (R5)
**Soubor:** `app/Http/Controllers/ScreenshotRenderController.php` a `app/Http/Middleware/DetectScreenshotMode.php`

```php
// ScreenshotRenderController.php
// ODSTRANĚNO: Auth::loginUsingId($userId);

// DetectScreenshotMode.php
// ZMĚNĚNO: Auth::guard($guard)->onceUsingId($userId);
// ZMĚNĚNO: $request->session()->flash(...) místo put() a save()
```

## 6. XSS ve vyhledávání (R6)
**Soubor:** `app/Services/Help/HelpSearchService.php`

```php
protected function highlight(string $text, string $query): string
{
    $escapedText = e($text);
    $escapedQuery = e($query);
    // ... preg_replace nad escapovaným textem ...
}
```
