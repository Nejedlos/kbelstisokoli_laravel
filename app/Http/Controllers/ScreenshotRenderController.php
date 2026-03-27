<?php

namespace App\Http\Controllers;

use App\Support\ScreenshotMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ScreenshotRenderController extends Controller
{
    /**
     * Renderuje cílovou URL v screenshot režimu.
     * Tato metoda slouží jako proxy, která interně zavolá cílovou URL se screenshot parametry
     * a vrátí upravené HTML.
     */
    public function render(Request $request)
    {
        $targetUrl = $request->query('url');
        $userId = $request->query('user_id'); // ID uživatele, pro kterého renderujeme (volitelné)

        if (!$targetUrl) {
            return response()->json(['error' => 'Missing url parameter'], 400);
        }

        // 1. Validace cílové URL (musí být interní)
        if (!$this->isInternalUrl($targetUrl)) {
            \Illuminate\Support\Facades\Log::warning('[ScreenshotProxy] Blocked external URL attempt', [
                'url' => $targetUrl,
                'ip' => $request->ip()
            ]);
            return response()->json(['error' => 'Only internal URLs are allowed'], 403);
        }

        // 2. Kontrola signatury (pro externí volání z NASu)
        // Pokud request obsahuje user_id a target_path, musí být signatura platná,
        // jinak se k cizím datům nikdo nedostane.
        if ($userId && !$request->hasValidSignature()) {
            return response()->json(['error' => 'Invalid or expired signature'], 401);
        }

        // 3. Příprava URL se screenshot signálem a ID uživatele
        $screenshotUrl = $this->generateSignedScreenshotUrl($targetUrl, $userId);

        // 4. Interní request pro získání HTML
        // Předáme cookies aktuálního requestu (pokud je volá browser uživatele)
        // A přidáme autorizační token pro impersonifikaci cílové stránky
        $response = Http::withCookies($request->cookies->all(), parse_url($screenshotUrl, PHP_URL_HOST))
            ->withHeaders([
                'X-Screenshot-Mode' => '1',
                'X-Screenshot-Token' => config('screenshot.internal_token'),
                'User-Agent' => $request->userAgent() ?: 'Laravel Screenshot System',
            ])
            ->get($screenshotUrl);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to fetch target URL',
                'status' => $response->status(),
                'url' => $screenshotUrl
            ], 502);
        }

        $html = $response->body();

        // 5. Post-processing HTML (ujistíme se, že assety jsou absolutní)
        $html = $this->fixAssetUrls($html);

        return response($html)
            ->header('Content-Type', 'text/html; charset=UTF-8')
            ->header('X-Screenshot-Processed', '1');
    }

    /**
     * Kontrola, zda je URL interní.
     */
    protected function isInternalUrl(string $url): bool
    {
        $appUrl = config('app.url');

        // Povolit pouze pokud URL začíná na APP_URL nebo je to relativní cesta (která ale nezačíná na //)
        if (Str::startsWith($url, $appUrl)) {
            return true;
        }

        if (Str::startsWith($url, '/') && !Str::startsWith($url, '//')) {
            return true;
        }

        return false;
    }

    /**
     * Generuje podepsanou URL pro cílovou adresu.
     */
    protected function generateSignedScreenshotUrl(string $url, ?int $userId = null): string
    {
        // Převedeme na absolutní, pokud je relativní
        if (!Str::startsWith($url, 'http')) {
            $url = URL::to($url);
        }

        $params = [
            'target_path' => parse_url($url, PHP_URL_PATH),
        ];

        if ($userId) {
            $params['screenshot_user_id'] = $userId;
        }

        // Přidáme signaturu
        return URL::temporarySignedRoute(
            'screenshot.proxy', // Virtuální route name
            now()->addMinutes(5),
            $params
        );
    }

    /**
     * Opraví relativní URL assetů na absolutní.
     */
    protected function fixAssetUrls(string $html): string
    {
        $baseUrl = rtrim(config('app.url'), '/');

        // Fix pro src="/..." a href="/..."
        // Ale ignorujeme data: URIs a externí linky
        $html = preg_replace('/(src|href)=["\']\/(?!\/)([^"\']+)["\']/', '$1="' . $baseUrl . '/$2"', $html);

        // Fix pro Vite assety (pokud by se nějak prosmýkly relativní)
        // Ale standardní asset() by měl stačit.

        return $html;
    }
}
