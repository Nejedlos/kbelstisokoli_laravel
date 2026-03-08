<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddRequestIdToResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) \Illuminate\Support\Str::uuid();

        // Přidáme ID do requestu pro pozdější použití (např. v logu)
        $request->attributes->set('request_id', $requestId);

        /** @var Response $response */
        $response = $next($request);

        // Přidáme ID do hlaviček odpovědi
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
