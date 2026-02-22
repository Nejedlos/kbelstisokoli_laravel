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

        return back();
    }
}
