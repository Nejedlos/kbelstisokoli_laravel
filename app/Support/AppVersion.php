<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

class AppVersion
{
    public static function get(): string
    {
        return Cache::remember('app_version', 3600, function () {
            $version = config('app.version', '1.0.0');

            // Try to get git commit hash if available
            if (file_exists(base_path('.git'))) {
                try {
                    $hash = trim(exec('git rev-parse --short HEAD'));
                    if ($hash) {
                        return "{$version}-{$hash}";
                    }
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            // Fallback to env or constant
            return env('APP_VERSION', $version);
        });
    }
}
