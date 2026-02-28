<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use voku\helper\HtmlMin;

class MinifyHtmlMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Respektujeme výkonnostní config
        if (! config('performance.features.html_minification', false)) {
            return $response;
        }

        // Nemínifikujeme v administraci (Filament/Livewire stabilita)
        if ($request->is('admin*')) {
            return $response;
        }

        if ($this->shouldMinify($response)) {
            $content = $response->getContent();

            $htmlMin = new HtmlMin;

            // Extreme cleaning for "perfect" output using valid library methods
            $htmlMin->doRemoveComments(true);
            $htmlMin->doSumUpWhitespace(true);
            $htmlMin->doRemoveWhitespaceAroundTags(true);
            $htmlMin->doOptimizeAttributes(true);
            $htmlMin->doRemoveHttpPrefixFromAttributes(true);
            $htmlMin->doRemoveHttpsPrefixFromAttributes(true);
            $htmlMin->doKeepHttpAndHttpsPrefixOnExternalAttributes(true);
            $htmlMin->doRemoveDefaultAttributes(true);
            $htmlMin->doRemoveDeprecatedAnchorName(true);
            $htmlMin->doRemoveDeprecatedScriptCharsetAttribute(true);
            $htmlMin->doRemoveDeprecatedTypeFromStyleAndLinkTag(true);
            $htmlMin->doRemoveDefaultMediaTypeFromStyleAndLinkTag(true);
            $htmlMin->doRemoveDefaultTypeFromButton(true);
            $htmlMin->doRemoveEmptyAttributes(true);
            $htmlMin->doSortHtmlAttributes(true);
            $htmlMin->doSortCssClassNames(true);
            $htmlMin->doOptimizeViaHtmlDomParser(true);
            $htmlMin->doRemoveSpacesBetweenTags(true);
            $htmlMin->doRemoveOmittedHtmlTags(true);
            $htmlMin->doRemoveOmittedQuotes(true);
            $htmlMin->doRemoveValueFromEmptyInput(true);

            $content = $htmlMin->minify($content);

            $response->setContent($content);
            $response->headers->set('X-HTML-Minified', 'yes');
        }

        return $response;
    }

    protected function shouldMinify($response): bool
    {
        // Accept any Symfony Response (includes Laravel and Livewire responses)
        if (! $response instanceof Response) {
            return false;
        }

        $contentType = $response->headers->get('Content-Type');

        return $contentType && str_contains($contentType, 'text/html');
    }
}
