<?php

namespace App\Support;

final class ErrorReportSanitizer
{
    private const HIDDEN = '[hidden]';

    /**
     * Recursively remove credentials from application error reports while
     * retaining non-sensitive diagnostic context.
     *
     * @param  array<array-key, mixed>  $data
     * @return array<array-key, mixed>
     */
    public static function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            if (self::isSensitiveKey((string) $key)) {
                $data[$key] = self::HIDDEN;
            } elseif (is_array($value)) {
                $data[$key] = self::sanitize($value);
            } elseif (is_string($value)) {
                $data[$key] = self::sanitizeUrl($value);
            }
        }

        return $data;
    }

    public static function sanitizeUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        $separator = '(?:/|%2f)';
        $schedulerPath = $separator
            .self::encodedWordPattern('system')
            .$separator
            .self::encodedWordPattern('schedule')
            .$separator;

        $sanitized = preg_replace(
            '~('.$schedulerPath.')[^/?#\s]+~i',
            '$1'.self::HIDDEN,
            $url,
        ) ?? $url;

        return preg_replace_callback(
            '/([?&])([^=&#]+)=([^&#]*)/',
            static function (array $matches): string {
                $key = trim(strtolower(rawurldecode($matches[2])), '[]');

                if (! self::isSensitiveKey($key)) {
                    return $matches[0];
                }

                return $matches[1].$matches[2].'='.self::HIDDEN;
            },
            $sanitized,
        ) ?? $sanitized;
    }

    private static function isSensitiveKey(string $key): bool
    {
        return preg_match(
            '/(?:^|[^a-z0-9])(authorization|cookie|password|secret|signature|token)(?:$|[^a-z0-9])/i',
            $key,
        ) === 1;
    }

    private static function encodedWordPattern(string $word): string
    {
        return implode('', array_map(
            static fn (string $character): string => sprintf('(?:%s|%%%02x)', $character, ord($character)),
            str_split($word),
        ));
    }
}
