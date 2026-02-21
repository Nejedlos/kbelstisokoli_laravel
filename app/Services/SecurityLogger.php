<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

class SecurityLogger
{
    /**
     * Logování bezpečnostní události.
     */
    public static function log(string $event, array $data = []): void
    {
        $context = array_merge([
            'ip' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'user_id' => auth()->id(),
        ], $data);

        Log::channel('daily')->info("SECURITY_EVENT: {$event}", $context);
    }
}
