<?php

namespace App\Support;

use App\Support\Icons\AppIcon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

/**
 * Robustní správa ikon pro Filament.
 * Zajišťuje validaci, fallbacky a integraci s Blade Icons.
 */
class FilamentIcon
{
    /**
     * Získá název ikony pro Blade Icons.
     *
     * @param string|AppIcon $icon Klíč ikony (Enum) nebo název ikony
     * @param string $style Požadovaný styl (fal, fas, far, fab, fad, fat)
     * @param string $fallback Bezpečná ikona pro případ chyby
     * @return string
     */
    public static function get(string|AppIcon $icon, string $style = 'fal', string $fallback = 'heroicon-o-question-mark-circle'): string
    {
        // 1. Získání základního názvu ikony
        $iconName = match (true) {
            $icon instanceof AppIcon => $icon->value,
            is_string($icon) => self::normalize($icon),
            default => 'question',
        };

        // 2. Pokud už má prefix (např. heroicon-), vrátíme ji přímo
        if (Str::contains($iconName, ['-'])) {
             $parts = explode('-', $iconName);
             $prefix = $parts[0];
             if (in_array($prefix, ['heroicon', 'fas', 'far', 'fab', 'fal', 'fad', 'fat', 'app'])) {
                 return $iconName;
             }
        }

        // 3. Validace stylu a Pro fallback
        $finalStyle = self::resolveStyle($style);

        // 4. Sestavení výsledného názvu pro Blade Icons
        return "{$finalStyle}-{$iconName}";
    }

    /**
     * Normalizuje název ikony (podtržítka na pomlčky).
     */
    public static function normalize(string $icon): string
    {
        return str_replace('_', '-', $icon);
    }

    /**
     * Rozhodne o finálním stylu s ohledem na dostupnost Pro ikon.
     */
    protected static function resolveStyle(string $style): string
    {
        $style = strtolower($style);
        $proStyles = ['fal', 'fad', 'fat'];

        // Pokud je vyžadován Pro styl, ale nemáme Pro (zde detekujeme absenci KIT nebo PRO balíčku)
        // V tomto projektu předpokládáme Free, dokud není v configu/env řečeno jinak.
        if (in_array($style, $proStyles)) {
            if (!config('app.fontawesome_pro', false)) {
                // Fallback na Solid, který je vždy dostupný ve Free
                return 'fas';
            }
        }

        $allowedStyles = ['fas', 'far', 'fab', 'fal', 'fad', 'fat'];
        return in_array($style, $allowedStyles) ? $style : 'fas';
    }

    /**
     * Bezpečné získání ikony. Pokud nastane chyba, vrátí fallback.
     */
    public static function safe(string|AppIcon $icon, string $fallback = 'heroicon-o-question-mark-circle'): string
    {
        try {
            return self::get($icon);
        } catch (\Exception $e) {
            Log::warning("Icon not found: " . (is_string($icon) ? $icon : $icon->value));
            return $fallback;
        }
    }

    /**
     * Vyrenderuje ikonu jako HTML (webfont styl).
     * Používejte pouze tam, kde nelze použít Blade Icons / SVG (např. starší HTML šablony).
     */
    public static function render(string|AppIcon $icon, string $style = 'fal'): HtmlString
    {
        $iconName = $icon instanceof AppIcon ? $icon->value : self::normalize($icon);
        $styleClass = match ($style) {
            'fal' => 'fa-light',
            'fas' => 'fa-solid',
            'far' => 'fa-regular',
            'fab' => 'fa-brands',
            'fad' => 'fa-duotone',
            'fat' => 'fa-thin',
            default => 'fa-solid',
        };

        return new HtmlString("<i class=\"{$styleClass} fa-{$iconName} fa-fw\"></i>");
    }

    // --- Aliasy pro pohodlí ---

    public static function solid(string|AppIcon $icon): string
    {
        return self::get($icon, 'fas');
    }

    public static function regular(string|AppIcon $icon): string
    {
        return self::get($icon, 'far');
    }

    public static function light(string|AppIcon $icon): string
    {
        return self::get($icon, 'fal');
    }
}
