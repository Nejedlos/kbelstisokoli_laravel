<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class HelpUrlHelper
{
    /**
     * Generuje URL pro nápovědu podle kontextu.
     *
     * @param array $params
     * @return string
     */
    public static function getUrl(array $params = []): string
    {
        // Pokud jsme v členské sekci (prefix clenska-sekce nebo jméno member.*)
        if (request()->is('clenska-sekce*') || (Route::current() && str_starts_with(Route::currentRouteName(), 'member.'))) {
            return route('member.help', $params);
        }

        // Výchozí je Filament nápověda
        try {
            return \App\Filament\Pages\Help::getUrl($params);
        } catch (\Throwable $e) {
            // Fallback pro případy, kdy Filament není dostupný nebo route neexistuje
            return route('member.help', $params);
        }
    }
}
