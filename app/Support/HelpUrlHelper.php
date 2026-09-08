<?php

namespace App\Support;

use App\Filament\Pages\Help;
use Illuminate\Support\Facades\Route;

class HelpUrlHelper
{
    /**
     * Generuje URL pro nápovědu podle kontextu.
     */
    public static function getUrl(array $params = []): string
    {
        // Pokud jsme v členské sekci (prefix clenska-sekce nebo jméno member.*)
        if (request()->is('clenska-sekce*') || (Route::current() && str_starts_with(Route::currentRouteName(), 'member.'))) {
            return route('member.help', $params);
        }

        // Výchozí je Filament nápověda
        try {
            return Help::getUrl($params);
        } catch (\Throwable $e) {
            // Fallback pro případy, kdy Filament není dostupný nebo route neexistuje
            return route('member.help', $params);
        }
    }
}
