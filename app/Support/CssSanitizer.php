<?php

namespace App\Support;

class CssSanitizer
{
    public static function convertUnsupportedColorFunctions(string $value, string $fallback = 'transparent'): string
    {
        return preg_replace('/(oklab|oklch)\s*\([^)]+\)/i', $fallback, $value) ?? $value;
    }

    public static function sanitizeCssValue(string $value): string
    {
        $v = self::convertUnsupportedColorFunctions($value);
        // Další jednoduché fallbacky lze přidat později
        return $v;
    }
}
