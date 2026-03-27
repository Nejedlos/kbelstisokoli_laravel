<?php

namespace App\Support;

class HtmlSanitizer
{
    /**
     * Jednoduchá sanitizace HTML pro zamezení XSS (inline eventy, nebezpečné protokoly).
     * Pro komplexnější potřeby je vhodnější mews/purifier (HTMLPurifier).
     */
    public static function clean(?string $html, bool $allowScripts = false): string
    {
        if (!$html) {
            return '';
        }

        $dom = $html;

        // 1. Odstranění skriptů, pokud nejsou povoleny
        if (!$allowScripts) {
            $dom = preg_replace('/<script\b[^>]*>([\s\S]*?)<\/script>/i', '', $dom);
        }

        // 2. Odstranění všech on* event handlerů (nejčastější vektor XSS v atributech)
        $dom = preg_replace('/\s+on\w+="[^"]*"/i', '', $dom);
        $dom = preg_replace('/\s+on\w+=\'[^\']*\'/i', '', $dom);

        // 3. Odstranění javascript: v href/src
        $dom = preg_replace('/(href|src)\s*=\s*["\']javascript:[^"\']*["\']/i', '$1="#"', $dom);

        // 4. Odstranění <iframe src="javascript:...">
        $dom = preg_replace('/<iframe[^>]*src\s*=\s*["\']javascript:[^"\']*["\'][^>]*>/i', '', $dom);

        return $dom;
    }
}
