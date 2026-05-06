<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NormalizeSignedUrlParameters
{
    /**
     * Odstraní nežádoucí query parametry z podepsaných URL (např. UTM parametry
     * přidané e-mailovými klienty), které by jinak rozbily validaci podpisu.
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // Cílíme pouze na citlivé podepsané trasy, zejména Filament reset hesla
            if ($request->routeIs('filament.*.auth.password-reset.reset') ||
                $request->is('admin/password-reset/reset')) {

                $allowed = [
                    'signature', // podpis
                    'expires',   // pro dočasně podepsané URL (kompatibilita)
                    'email',
                    'token',
                ];

                $original = $request->query->all();
                $filtered = array_intersect_key($original, array_flip($allowed));

                if ($original !== $filtered) {
                    // Pro ladění si zapíšeme, které klíče byly odstraněny
                    \Log::info('NormalizeSignedUrlParameters.strip', [
                        'route' => optional($request->route())->getName(),
                        'removed' => array_values(array_diff(array_keys($original), array_keys($filtered))),
                    ]);

                    $request->query->replace($filtered);
                }
            }
        } catch (\Throwable $e) {
            // Tichý fail – nikdy nesmí zablokovat průchod požadavku
            \Log::warning('NormalizeSignedUrlParameters.failed', [
                'error' => $e->getMessage(),
            ]);
        }

        return $next($request);
    }
}
