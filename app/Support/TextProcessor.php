<?php

namespace App\Support;

use Illuminate\Support\Facades\Lang;

class TextProcessor
{
    /**
     * Nahradí generovaný text v popisu motivačním citátem.
     * Identifikuje text jako "Původní ID: XXXX Sport: basket".
     */
    public static function enhanceDescription(?string $description, ?int $seedId = null): string
    {
        if (empty($description)) {
            return '';
        }

        // Hledáme vzor "Původní ID: [číslo]\nSport: basket" (včetně možných HTML tagů)
        // Uživatelský vzor ze zadání: Původní ID: 2067 Sport: basket
        $pattern = '/Původní ID:\s*\d+\s*(<br\s*\/?>)?\s*Sport:\s*basket/iu';

        // Pokud tam jsou HTML tagy, musíme je vzít v úvahu
        $description = preg_replace($pattern, '##MOTIVATIONAL_QUOTE##', $description);

        if (str_contains($description, '##MOTIVATIONAL_QUOTE##')) {
            $quotes = Lang::get('motivational.quotes');

            if (is_array($quotes) && count($quotes) > 0) {
                // Použijeme seedId pro výběr citátu, aby byl výsledek idempotentní pro danou akci
                $index = $seedId ? ($seedId % count($quotes)) : array_rand($quotes);
                $quote = $quotes[$index];

                // Nahradíme nalezený text citátem obaleným v bloku pro zvýraznění
                $replacement = '<blockquote class="border-l-4 border-primary pl-4 italic my-6 text-slate-600">' . $quote . '</blockquote>';

                return str_replace('##MOTIVATIONAL_QUOTE##', $replacement, $description);
            }
        }

        return $description;
    }
}
