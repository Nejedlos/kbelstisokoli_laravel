<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorMailThrottle
{
    /**
     * Check if the error mail should be throttled based on fingerprint.
     *
     * @return bool True if should be throttled (suppressed), false if should be sent.
     */
    public static function shouldThrottle(Throwable $e, ?string $url = null): bool
    {
        // 1. Check if deduplication is enabled globally
        if (! config('mail.error_reporting.dedup_enabled', true)) {
            return false;
        }

        // 2. Check if current environment should be deduped
        $dedupEnvs = config('mail.error_reporting.dedup_environments', ['production', 'staging']);
        $currentEnv = config('app.env');
        if (! in_array($currentEnv, $dedupEnvs)) {
            // Local development override: force always send if configured
            if ($currentEnv === 'local' && config('mail.error_reporting.always_send', false)) {
                return false;
            }

            // In other environments (local/testing etc. that are NOT in dedupEnvs), we don't throttle by default
            return false;
        }

        // 3. Generate stable fingerprint
        $fingerprint = self::generateFingerprint($e, $url);
        $ttl = (int) config('mail.error_reporting.dedup_ttl', 900);

        // If TTL is 0 or less, we don't throttle
        if ($ttl <= 0) {
            return false;
        }

        $key = 'error_mail_throttle:'.$fingerprint;

        // 4. Check if we already sent this error within the TTL window
        if (Cache::has($key)) {
            self::logSuppression($fingerprint, $e);

            return true;
        }

        // 5. Store fingerprint in cache to throttle future occurrences
        Cache::put($key, true, $ttl);

        return false;
    }

    /**
     * Generate a stable fingerprint for the exception.
     * Fingerprint is based on class, message (normalized), file, line, and URL.
     */
    private static function generateFingerprint(Throwable $e, ?string $url = null): string
    {
        $class = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();

        // Normalize message: remove dynamic parts like IDs, UUIDs, timestamps if they look common
        // Example: "User 123 not found" -> "User [ID] not found"
        // For now we do basic normalization of hex-looking strings (UUIDs, hashes) and digits.
        $normalizedMessage = preg_replace('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}/i', '[UUID]', $message);
        $normalizedMessage = preg_replace('/\d+/', '[ID]', $normalizedMessage);

        $data = [
            $class,
            $normalizedMessage,
            $file,
            $line,
            $url,
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * Log that an email notification was suppressed.
     */
    private static function logSuppression(string $fingerprint, Throwable $e): void
    {
        // We use a separate cache key to rate-limit the suppression logs themselves
        // so we don't flood the main log file with thousands of "suppressed" entries.
        $logKey = 'error_mail_suppressed_log:'.$fingerprint;

        if (! Cache::has($logKey)) {
            Log::info('Error mail suppressed (deduped)', [
                'fingerprint' => $fingerprint,
                'exception' => get_class($e),
                'file' => $e->getFile().':'.$e->getLine(),
                'ttl' => config('mail.error_reporting.dedup_ttl', 900),
            ]);

            // Only log suppression every 60 seconds for the same fingerprint
            Cache::put($logKey, true, 60);
        }
    }
}
