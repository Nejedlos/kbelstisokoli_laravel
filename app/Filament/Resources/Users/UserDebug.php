<?php

namespace App\Filament\Resources\Users;

use Illuminate\Support\Facades\Log;

class UserDebug
{
    public static function log(string $message, array $context = []): void
    {
        Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/user-debug.log'),
        ])->debug($message, $context);
    }
}
