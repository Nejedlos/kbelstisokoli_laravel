<?php

namespace App\Services;

use Illuminate\Http\Request;

class DeviceContextService
{
    /** Browser-reported diagnostics, never an authorization or identity signal. */
    public function collect(Request $request): array
    {
        $hints = [];
        foreach (['Sec-CH-UA', 'Sec-CH-UA-Mobile', 'Sec-CH-UA-Platform', 'Sec-CH-UA-Model', 'Sec-CH-UA-Platform-Version', 'Sec-CH-UA-Full-Version-List'] as $header) {
            $value = $this->clean($request->header($header), 1024);
            if ($value !== null) {
                $hints[$header] = $value;
            }
        }

        return [
            'source' => 'request_headers',
            'user_agent' => $this->clean($request->userAgent(), 2048),
            'model' => $this->quotedValue($hints['Sec-CH-UA-Model'] ?? null),
            'platform' => $this->quotedValue($hints['Sec-CH-UA-Platform'] ?? null),
            'platform_version' => $this->quotedValue($hints['Sec-CH-UA-Platform-Version'] ?? null),
            'mobile' => match ($hints['Sec-CH-UA-Mobile'] ?? null) {
                '?1' => true,
                '?0' => false,
                default => null,
            },
            'client_hints' => $hints,
        ];
    }

    private function clean(?string $value, int $limit): ?string
    {
        $value = trim(preg_replace('/[\x00-\x1F\x7F]/', '', mb_substr($value ?? '', 0, $limit)));

        return $value === '' ? null : $value;
    }

    private function quotedValue(?string $value): ?string
    {
        $decoded = json_decode($value ?? '', true);

        return is_string($decoded) ? $this->clean($decoded, 1024) : null;
    }
}
