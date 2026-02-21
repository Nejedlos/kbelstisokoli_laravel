<?php

if (!function_exists('media_url')) {
    /**
     * Získá URL k médiu z knihovny.
     */
    function media_url($id, string $conversion = ''): ?string
    {
        if (!$id) {
            return null;
        }

        $asset = \App\Models\MediaAsset::find($id);

        if (!$asset) {
            return null;
        }

        return $asset->getUrl($conversion);
    }
}

if (!function_exists('media_alt')) {
    /**
     * Získá Alt text k médiu z knihovny.
     */
    function media_alt($id): string
    {
        if (!$id) {
            return '';
        }

        $asset = \App\Models\MediaAsset::find($id);

        return $asset?->alt_text ?: '';
    }
}
