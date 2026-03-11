<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LanguageController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $lang)
    {
        $supportedLocales = config('app.supported_locales', ['cs', 'en']);

        if (in_array($lang, $supportedLocales)) {
            session(['locale' => $lang]);
            cookie()->queue(cookie()->forever('filament_language_switch_locale', $lang));
        }

        // Robustní redirect: Pokud předchozí URL obsahovala ?lang=..., vyčistíme to,
        // aby tento parametr nepřepsal nově zvolenou preferenci v SetLocaleMiddleware.
        $url = url()->previous();
        if ($url) {
            $parsed = parse_url($url);
            if (isset($parsed['query'])) {
                parse_str($parsed['query'], $query);
                if (isset($query['lang'])) {
                    unset($query['lang']);
                    $newQuery = http_build_query($query);
                    $url = (isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '')
                        . (isset($parsed['host']) ? $parsed['host'] : '')
                        . (isset($parsed['port']) ? ':' . $parsed['port'] : '')
                        . (isset($parsed['path']) ? $parsed['path'] : '')
                        . ($newQuery ? '?' . $newQuery : '')
                        . (isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '');

                    return redirect()->to($url);
                }
            }
        }

        return back();
    }
}
