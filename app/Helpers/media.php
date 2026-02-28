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
    function web_asset(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        // Absolutní URL ponecháme jak je
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $normalized = ltrim($path, '/');

        // 1) Nový režim: soubory přímo v public/
        if (file_exists(public_path($normalized))) {
            return asset($normalized);
        }

        // 2) Zpětná kompatibilita: staré soubory ve storage/ (symlink)
        if (file_exists(public_path('storage/'.$normalized))) {
            return asset('storage/'.$normalized);
        }

        // 3) Fallback – vrať asset s původní cestou
        return asset($normalized);
    }
}
