<?php

if (!function_exists('brand_text')) {
    /**
     * Nahradí zástupné symboly brandingu v textu nebo poli.
     */
    function brand_text(string|array|null $data): string|array
    {
        $service = app(\App\Services\BrandingService::class);

        if (is_array($data)) {
            return $service->replaceInArray($data);
        }

        return $service->replacePlaceholders($data);
    }
}
