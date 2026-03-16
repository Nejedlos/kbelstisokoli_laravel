<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InjectFeedbackWidget
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!$this->shouldInject($request, $response)) {
            return $response;
        }

        $this->injectWidget($response);

        return $response;
    }

    protected function shouldInject(Request $request, Response $response): bool
    {
        if (!config('feedback.enabled', true)) {
            return false;
        }

        // 1. MUST be HTML response
        $contentType = $response->headers->get('Content-Type');
        if (!str_contains((string)$contentType, 'text/html')) {
            return false;
        }

        // 2. Skip AJAX/JSON/Widget requests
        if ($request->isXmlHttpRequest() || $request->expectsJson() || $request->routeIs('feedback.widget')) {
            return false;
        }

        // 3. Skip redirects and special responses
        if ($response instanceof \Symfony\Component\HttpFoundation\RedirectResponse ||
            $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse ||
            $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
            return false;
        }

        // 4. Always inject in Debug mode (except in tests to avoid noisy output)
        if (config('app.debug') && !app()->environment('testing')) {
            return true;
        }

        // 5. Host-based logic (for guests)
        $host = $request->getHost();
        $isTestHost = str_contains($host, 'new.') || str_contains($host, '.new.') || str_contains($host, 'staging.') || str_contains($host, 'dev.') || str_contains($host, '.test') || str_contains($host, 'localhost');

        if (!Auth::check() && !app()->environment('local', 'staging') && !$isTestHost) {
            return false;
        }

        return true;
    }

    protected function injectWidget(Response $response): void
    {
        $content = $response->getContent();

        if (str_contains($content, 'ks-fb-loader') || str_contains($content, 'ks-fb-root')) {
            return; // Již injektováno nebo přítomno
        }

        try {
            $widgetUrl = route('feedback.widget');
            $manifestPath = public_path('build/manifest.json');
            $jsUrl = '';

            if (file_exists($manifestPath)) {
                $manifest = json_decode(file_get_contents($manifestPath), true);
                if (isset($manifest['resources/js/feedback-widget.js']['file'])) {
                    $jsUrl = asset('build/' . $manifest['resources/js/feedback-widget.js']['file']);
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Feedback widget route or asset not found, skipping injection: ' . $e->getMessage());
            return;
        }

        if (empty($jsUrl)) {
            \Illuminate\Support\Facades\Log::warning('Feedback widget JS not found in manifest at ' . $manifestPath . ', skipping injection.');
            return;
        }

        $loader = <<<HTML
<script id="ks-fb-loader" data-navigate-once>
    (function() {
        let isFeedbackLoading = false;

        function loadFeedback() {
            if (isFeedbackLoading || document.getElementById('ks-fb-root')) {
                return;
            }
            isFeedbackLoading = true;

            // 1. Load the script first (if not already loaded)
            if (!window.ksFeedbackWidgetRegistered && !document.querySelector('script[src*="feedback-widget"]')) {
                const script = document.createElement('script');
                script.src = '{$jsUrl}';
                script.type = 'module';
                script.async = true;
                document.head.appendChild(script);
            }

            // 2. Fetch the widget HTML
            fetch('{$widgetUrl}')
                .then(response => response.text())
                .then(html => {
                    if (!html || document.getElementById('ks-fb-root')) return;
                    const temp = document.createElement('div');
                    temp.innerHTML = html.trim();
                    const widgetRoot = temp.querySelector('#ks-fb-root');

                    if (!widgetRoot) {
                        console.error('Feedback widget: Root element not found in response');
                        return;
                    }

                    // Wait for Alpine to be ready and component to be registered
                    let attempts = 0;
                    const inject = () => {
                        attempts++;
                        if (window.Alpine && window.ksFeedbackWidgetRegistered) {
                             if (!document.getElementById('ks-fb-root')) {
                                document.body.appendChild(widgetRoot);
                                console.log('Feedback widget: Injected into DOM after ' + attempts + ' attempts');
                                if (window.Alpine.initTree) {
                                    window.Alpine.initTree(widgetRoot);
                                }
                             }
                        } else if (attempts < 100) {
                            setTimeout(inject, 50);
                        } else {
                            console.warn('Feedback widget: Alpine or component not ready after 5s, giving up injection');
                        }
                    };
                    inject();
                })
                .finally(() => {
                    isFeedbackLoading = false;
                });
        }

        if (document.readyState !== 'loading') {
            loadFeedback();
        } else {
            document.addEventListener('DOMContentLoaded', loadFeedback);
        }

        // Pro jistotu i na load a Livewire navigaci
        window.addEventListener('load', loadFeedback);
        document.addEventListener('livewire:navigated', () => setTimeout(loadFeedback, 200));
    })();
</script>
HTML;

        $pos = strripos($content, '</body>');

        if (false !== $pos) {
            $content = substr($content, 0, $pos) . $loader . substr($content, $pos);
        } else {
            // Fallback: append to the end
            $content .= $loader;
        }

        $response->setContent($content);
    }
}
