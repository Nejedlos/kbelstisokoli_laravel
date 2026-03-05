<?php

namespace App\Services\Support;

use Illuminate\Support\Facades\Cache;

class ConsoleService
{
    protected const CACHE_KEY = 'debug_console_output';
    protected const MAX_LINES = 500;

    public static function log(string $message, string $type = 'info'): void
    {
        $timestamp = now()->format('H:i:s');
        $color = match ($type) {
            'error' => 'text-red-500',
            'warning' => 'text-yellow-500',
            'success' => 'text-green-500',
            'info' => 'text-blue-400',
            'debug' => 'text-gray-500',
            default => 'text-gray-300',
        };

        $line = sprintf(
            "[%s] <span class='%s'>%s</span>\n",
            $timestamp,
            $color,
            e($message)
        );

        $current = Cache::get(self::CACHE_KEY, '');
        $lines = explode("\n", $current);

        if (count($lines) > self::MAX_LINES) {
            $lines = array_slice($lines, -self::MAX_LINES);
        }

        $lines[] = rtrim($line);
        $newContent = implode("\n", array_filter($lines));

        Cache::put(self::CACHE_KEY, $newContent, now()->addHours(2));
    }

    public static function getContent(): string
    {
        return Cache::get(self::CACHE_KEY, '');
    }

    public static function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
