<?php

namespace App\Support;

use Illuminate\Http\Request;

class ScreenshotMode
{
    protected static bool $isActive = false;

    protected static ?int $userId = null;

    /**
     * Aktivuje screenshot režim.
     */
    public static function activate(?int $userId = null): void
    {
        static::$isActive = true;
        static::$userId = $userId;
    }

    public static function deactivate(): void
    {
        static::$isActive = false;
        static::$userId = null;
    }

    /**
     * Získá ID uživatele, pro kterého je režim aktivován.
     */
    public static function getUserId(): ?int
    {
        return static::$userId;
    }

    /**
     * Zjistí, zda je aktivní screenshot režim.
     */
    public static function isActive(): bool
    {
        return static::$isActive;
    }

    /**
     * Zjistí, zda by měl být režim detekován z aktuálního requestu.
     */
    public static function shouldActivate(Request $request): bool
    {
        // 1. Přímý query parametr (např. pro lokální testy)
        if ($request->query('screenshot') === '1') {
            return true;
        }

        // 2. Speciální header
        if ($request->hasHeader('X-Screenshot-Mode')) {
            return true;
        }

        // 3. Kontrola podepsané URL (nejbezpečnější pro externí služby)
        // Musíme ověřit signaturu, pokud je přítomna
        if ($request->hasValidSignature()) {
            return true;
        }

        // 4. Interní tajný token z configu (pokud je nastaven)
        $token = config('screenshot.internal_token');
        if ($token && $request->header('X-Screenshot-Token') === $token) {
            return true;
        }

        return false;
    }
}
