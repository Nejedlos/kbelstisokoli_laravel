<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.ltrim($request->getPathInfo(), '/');

        // 1. Hledání přesného match (nejrychlejší)
        $redirect = Redirect::where('is_active', true)
            ->where('source_path', $path)
            ->where('match_type', 'exact')
            ->orderBy('priority', 'desc')
            ->first();

        // 2. Pokud není exact, hledáme prefix match (volitelně)
        if (! $redirect) {
            $redirects = Redirect::where('is_active', true)
                ->where('match_type', 'prefix')
                ->orderBy('priority', 'desc')
                ->orderByRaw('LENGTH(source_path) DESC')
                ->get();

            foreach ($redirects as $pRedirect) {
                if (str_starts_with($path, $pRedirect->source_path)) {
                    $redirect = $pRedirect;
                    break;
                }
            }
        }

        if ($redirect) {
            \Illuminate\Support\Facades\Log::info('RedirectMiddleware.match', [
                'path' => $path,
                'source' => $redirect->source_path,
                'target_path' => $redirect->target_path,
                'target_url' => $redirect->target_url,
                'target_type' => $redirect->target_type,
            ]);

            $target = $redirect->target_type === 'internal'
                ? url($redirect->target_path)
                : $redirect->target_url;

            // Anti-loop ochrana: Pokud je cíl stejný jako zdroj, nepokračujeme v redirectu
            if ($target === $request->fullUrl() || $redirect->target_path === $path) {
                return $next($request);
            }

            // Inkrementace statistik (pro jednoduchost bez queue, v reálu vhodné optimalizovat)
            $redirect->increment('hits_count');
            $redirect->update(['last_hit_at' => now()]);

            return redirect()->to($target, $redirect->status_code);
        }

        return $next($request);
    }
}
