<?php

namespace App\Http\Controllers;

use App\Support\ScreenshotMode;
use Illuminate\Http\Request;
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

        if (!$targetUrl) {
            return response()->json(['error' => 'Missing url parameter'], 400);
        }

        // 1. Validace cílové URL (musí být interní)
        if (!$this->isInternalUrl($targetUrl)) {
            return response()->json(['error' => 'Only internal URLs are allowed'], 403);
        }

        // 2. Příprava URL se screenshot signálem
        // Použijeme signed URL, aby cílová stránka (DetectScreenshotMode middleware) věděla, že má aktivovat režim
        $screenshotUrl = $this->generateSignedScreenshotUrl($targetUrl);

        // 3. Interní request pro získání HTML
        // Předáme cookies aktuálního requestu, aby byl zachován login uživatele (pokud volá admin přes feedback)
        $response = Http::withCookies($request->cookies->all(), parse_url($screenshotUrl, PHP_URL_HOST))
            ->withHeaders(['X-Screenshot-Mode' => '1'])
            ->get($screenshotUrl);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to fetch target URL',
                'status' => $response->status(),
                'url' => $screenshotUrl
            ], 502);
        }

        $html = $response->body();

        // 4. Post-processing HTML (ujistíme se, že assety jsou absolutní)
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
        $parsedApp = parse_url($appUrl);
        $parsedTarget = parse_url($url);

        // Pokud je to relativní cesta
        if (!isset($parsedTarget['host'])) {
            return true;
        }

        // Pokud je to absolutní URL, musí odpovídat hostiteli
        return $parsedTarget['host'] === $parsedApp['host'];
    }

    /**
     * Generuje podepsanou URL pro cílovou adresu.
     */
    protected function generateSignedScreenshotUrl(string $url): string
    {
        // Převedeme na absolutní, pokud je relativní
        if (!Str::startsWith($url, 'http')) {
            $url = URL::to($url);
        }

        // Přidáme signaturu
        // Protože URL::signedRoute pracuje s názvy rout, musíme to udělat manuálně přes URL generator
        return URL::temporarySignedRoute(
            'screenshot.proxy', // Virtuální route name
            now()->addMinutes(5),
            ['target_path' => parse_url($url, PHP_URL_PATH)]
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
