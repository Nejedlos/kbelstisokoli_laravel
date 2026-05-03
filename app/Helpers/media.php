<?php

if (! function_exists('media_url')) {
    /**
     * Získá URL k médiu z knihovny.
     */
    function media_url($id, string $conversion = ''): ?string
    {
        if (! $id) {
            return null;
        }

        $asset = \App\Models\MediaAsset::find($id);

        if (! $asset) {
            return null;
        }

        return $asset->getUrl($conversion);
    }
}

if (! function_exists('media_alt')) {
    /**
     * Získá Alt text k médiu z knihovny.
     */
    function media_alt($id): string
    {
        if (! $id) {
            return '';
        }

        $asset = \App\Models\MediaAsset::find($id);

        return $asset?->alt_text ?: '';
    }
}

if (! function_exists('web_asset')) {
    /**
     * Vrátí veřejnou URL k souboru nahranému do public složky.
     *
     * - Pokud $path začíná http/https, vrací se beze změny.
     * - Nejprve zkoušíme public_path($path), pokud neexistuje, zkusíme public_path('storage/'.$path)
     *   pro zpětnou kompatibilitu se staršími nahrávkami do storage/ s linkem.
     */
    function web_asset(?string $path, bool $tryWebp = true): ?string
    {
        if (! $path) {
            return null;
        }

        // Absolutní URL ponecháme jak je
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        // Funkce pro kontrolu existence a vrácení asset URL
        $checkAndReturn = function($p) use ($tryWebp) {
            $normalizedP = ltrim($p, '/');

            // 1) Zkusíme WebP variantu, pokud je to zapnuté
            if ($tryWebp) {
                $info = pathinfo($normalizedP);
                $webpPath = ($info['dirname'] !== '.' ? $info['dirname'] . '/' : '') . $info['filename'] . '.webp';
                if (file_exists(public_path($webpPath)) || @file_exists(base_path('public/' . $webpPath))) {
                    return asset($webpPath);
                }
            }

            // 2) Zkusíme původní cestu
            if (file_exists(public_path($normalizedP)) || @file_exists(base_path('public/' . $normalizedP))) {
                return asset($normalizedP);
            }

            return null;
        };

        // Postupně zkoušíme různé lokace:

        // A) Přímá cesta (včetně uploads/ pokud je v path)
        if ($res = $checkAndReturn($normalized)) return $res;

        // B) V uploads/ (pokud tam není)
        if (!str_starts_with($normalized, 'uploads/')) {
            if ($res = $checkAndReturn('uploads/' . $normalized)) return $res;
        }

        // C) V storage/ (symlink)
        if ($res = $checkAndReturn('storage/' . $normalized)) return $res;

        // D) Fallback na assets/img/loga/ (pro loga)
        if (str_contains($normalized, 'logo')) {
            $filename = basename($normalized);
            if ($res = $checkAndReturn('assets/img/loga/' . $filename)) return $res;
        }

        // E) Poslední záchrana – vrať asset s původní cestou (i když neexistuje)
        // Pokud jsme na produkci, zajistíme HTTPS pokud je v APP_URL
        $url = asset($normalized);
        if (str_contains(config('app.url', ''), 'https://') && str_starts_with($url, 'http://')) {
            $url = str_replace('http://', 'https://', $url);
        }

        return $url;
    }
}
