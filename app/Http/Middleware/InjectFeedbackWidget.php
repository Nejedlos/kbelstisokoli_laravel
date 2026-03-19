<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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

        // 0. Quick path for performance: skip images, css, js by extension before anything else
        $path = $request->getPathInfo();
        if (preg_match('/\.(jpg|jpeg|png|gif|svg|webp|css|js|map|woff|woff2|ttf|eot)$/i', $path)) {
            return false;
        }

        // 1. MUST be HTML response
        $contentType = $response->headers->get('Content-Type');
        if (!str_contains((string)$contentType, 'text/html')) {
            return false;
        }

        // 2. Skip AJAX/JSON/Widget/Livewire requests
        if ($request->isXmlHttpRequest() ||
            $request->expectsJson() ||
            $request->hasHeader('X-Livewire') ||
            $request->routeIs('feedback.*')) {
            return false;
        }

        // 3. Skip redirects, special responses and partials
        if ($response instanceof \Symfony\Component\HttpFoundation\RedirectResponse ||
            $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse ||
            $response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse ||
            $response->isServerError() ||
            $response->isClientError()) {
            return false;
        }

        // 4. Content check: MUST have </body> tag for reliable injection
        $content = $response->getContent();
        if (!str_contains($content, '</body>')) {
            return false;
        }

        // 4. Always inject in Debug mode (except in tests to avoid noisy output)
        if (config('app.debug') && !app()->runningUnitTests()) {
            return true;
        }

        // 5. Host-based logic (for guests)
        $host = $request->getHost();
        $isTestHost = str_contains($host, 'new.') || str_contains($host, '.new.') || str_contains($host, 'staging.') || str_contains($host, 'dev.') || str_contains($host, '.test') || str_contains($host, 'localhost');

        return $isTestHost;
    }

    protected function injectWidget(Response $response): void
    {
        $content = $response->getContent();

        if (str_contains($content, 'ks-fb-loader') || str_contains($content, 'ks-fb-root')) {
            return; // Již injektováno nebo přítomno
        }

        try {
            $widgetUrl = route('feedback.widget');
            $jsUrl = '';

            // Optimalizováno: Cachujeme URL widgetu v rámci requestu a framework cache, ale s ohledem na verzi manifestu
            $manifestPath = public_path('build/manifest.json');
            $mtime = file_exists($manifestPath) ? filemtime($manifestPath) : '0';
            $cacheKey = "feedback_widget_js_url_{$mtime}";

            $jsUrl = \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function() use ($manifestPath) {
                if (file_exists($manifestPath)) {
                    $manifest = json_decode(file_get_contents($manifestPath), true);
                    if (isset($manifest['resources/js/feedback-widget.js']['file'])) {
                        return asset('build/' . $manifest['resources/js/feedback-widget.js']['file']);
                    }
                }
                return '';
            });

            if (empty($jsUrl)) {
                return;
            }

            $cfg = [
                'strategy' => config('feedback.screenshot.strategy', 'auto'),
                'playwright' => [
                    'enabled' => config('feedback.screenshot.playwright.enabled', true),
                    'timeout' => config('feedback.screenshot.playwright.timeout', 30000),
                ],
                'endpoints' => [
                    'serverScreenshot' => Route::has('feedback.screenshot') ? route('feedback.screenshot') : null,
                ],
            ];
            $cfgJson = json_encode($cfg);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Feedback widget initialization failed, skipping injection: ' . $e->getMessage());
            return;
        }

        $loader = <<<HTML
<script id="ks-fb-config" class="ks-feedback-ignore">window.KS_FEEDBACK_CONFIG = {$cfgJson};</script>
<script id="ks-fb-loader" class="ks-feedback-ignore" data-navigate-once>
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
                document.head.appendChild(script);
            }

            // Fetch the widget HTML
            fetch('{$widgetUrl}')
                .then(response => response.text())
                .then(html => {
                    if (!html || document.getElementById('ks-fb-root')) return;

                    // Počkáme na registraci komponenty v Alpine
                    let attempts = 0;
                    const checkRegistration = () => {
                        attempts++;
                        if (window.ksFeedbackWidgetRegistered) {
                            const temp = document.createElement('div');
                            temp.innerHTML = html.trim();
                            const widgetRoot = temp.querySelector('#ks-fb-root');
                            if (widgetRoot) {
                                document.body.appendChild(widgetRoot);
                                // Pokud už Alpine běží, musíme ho upozornit na nový prvek
                                if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                                    window.Alpine.initTree(widgetRoot);
                                }
                            }
                        } else if (attempts < 100) {
                            setTimeout(checkRegistration, 50);
                            return; // Wait for the next check
                        } else {
                            console.error('Feedback widget: Alpine component registration timeout');
                        }
                        isFeedbackLoading = false;
                    };
                    checkRegistration();
                })
                .catch(err => {
                    console.error('Feedback widget load failed', err);
                    isFeedbackLoading = false;
                });
        }

        loadFeedback();
        document.addEventListener('livewire:navigated', loadFeedback);
    })();
</script>
HTML;

        // Bezpečné vložení před </body>
        // Použijeme replace, aby se zamezilo chybnému rozdělení stringu
        $pos = strripos($content, '</body>');

        if (false !== $pos) {
            $newContent = substr_replace($content, $loader . "\n</body>", $pos, 7);
            $response->setContent($newContent);
        }
    }
}
