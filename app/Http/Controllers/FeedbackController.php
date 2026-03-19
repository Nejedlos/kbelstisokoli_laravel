<?php

namespace App\Http\Controllers;

use App\Mail\FeedbackReportNotification;
use App\Models\FeedbackReport;
use App\Support\AppVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FeedbackController extends Controller
{
    public function renderWidget(Request $request): string
    {
        return view('partials.feedback-widget')->render();
    }

    public function snapshot(string $token): \Illuminate\View\View
    {
        // Snapshot token by měl být jednorázový a krátkodobý (např. 5 minut)
        $data = Cache::get("fb_snap_{$token}");

        if (!$data) {
            abort(404, 'Snapshot not found or expired.');
        }

        // Změna view pro renderování
        return view('feedback.snapshot', [
            'dom' => $data['dom'],
            'context' => $data['context'],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // 1. Rate limiting (v configu je '10,1' - 10 za minutu)
        // Laravel's throttle middleware is better handled in routes, but we can do a quick check here too if needed.

        $user = $request->user();

        // 2. Duplicate guard
        $titleHash = md5($request->input('title'));
        $descHash = md5($request->input('description'));
        $cacheKey = "feedback_dup_{$user->id}_" . md5($request->input('url')) . "_{$titleHash}_{$descHash}";

        if (Cache::has($cacheKey)) {
            return response()->json([
                'message' => 'Tento report jsme již přijali. Prosím, počkejte chvíli.',
            ], 429);
        }

        // 3. Validace
        $validated = $request->validate([
            'type' => 'required|in:bug,idea,feedback',
            'severity' => 'nullable|in:low,medium,high',
            'title' => 'required|string|max:120',
            'description' => 'required|string|max:5000',
            'steps' => 'nullable|string|max:10000',
            'include' => 'nullable|array',
            'context' => 'required|array',
            'context.url' => 'required|string',
            'context.route' => 'nullable|string',
            'context.area' => 'required|in:public,member,admin',
            'context.requestId' => 'nullable|string',
            'capture' => 'nullable|array',
            'capture.screenshot' => 'nullable|string',
            'capture.domLight' => 'nullable|string',
            'logs' => 'nullable|array',
            'logs.console' => 'nullable|array',
            'logs.errors' => 'nullable|array',
            'logs.network' => 'nullable|array',
            'logs.breadcrumbs' => 'nullable|array',
            'performance' => 'nullable|array',
        ]);

        // 4. Payload size guard (rough check)
        $payloadSize = strlen(serialize($request->all()));
        if ($payloadSize > config('feedback.limits.max_payload_bytes', 8388608)) {
             return response()->json([
                'message' => 'Payload je příliš velký. Zkuste prosím vypnout přiložení screenshotu nebo DOM snapshotu.',
            ], 413);
        }

        // 5. Redaction
        $redacted = $this->redactData($request->all());

        // 6. Uložení Reportu
        $report = new FeedbackReport();
        $report->user_id = $user->id;
        $report->type = $validated['type'];
        $report->severity = $validated['severity'] ?? null;
        $report->title = $validated['title'];
        $report->description = $validated['description'];
        $report->steps = $validated['steps'] ?? null;
        $report->url = $redacted['context']['url'] ?? $validated['context']['url'];
        $report->route_name = $redacted['context']['route'] ?? null;
        $report->locale = $redacted['context']['locale'] ?? app()->getLocale();
        $report->user_agent = $redacted['context']['device']['userAgent'] ?? $request->userAgent();
        $report->viewport = $redacted['context']['device']['viewport'] ?? null;
        $report->screen = $redacted['context']['device']['screen'] ?? null;
        $report->timezone = $redacted['context']['timezone'] ?? null;
        $report->source_area = $redacted['context']['area'] ?? $validated['context']['area'];
        $report->app_version = $redacted['context']['appVersion'] ?? AppVersion::get();
        $report->ip = $request->ip();
        $report->correlation_id = $redacted['context']['requestId'] ?? $request->attributes->get('request_id');
        $report->meta = array_merge($redacted['context'] ?? [], [
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames(),
            'include_options' => $redacted['include'] ?? [],
        ]);
        $report->save();

        // 7. Uložení souborů
        $storageDir = "feedback/{$report->id}";

        if (!empty($redacted['capture']['screenshot'])) {
            $screenshotData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $redacted['capture']['screenshot']));
            $path = "{$storageDir}/screenshot.jpg";
            Storage::disk('local')->put($path, $screenshotData);
            $report->screenshot_path = $path;
        }

        if (!empty($redacted['capture']['domLight'])) {
            $path = "{$storageDir}/dom.html";
            Storage::disk('local')->put($path, $redacted['capture']['domLight']);
            $report->dom_path = $path;
        }

        if (!empty($redacted['logs']['console']) || !empty($redacted['logs']['errors'])) {
            $path = "{$storageDir}/logs.json";
            Storage::disk('local')->put($path, json_encode([
                'console' => $redacted['logs']['console'] ?? [],
                'errors' => $redacted['logs']['errors'] ?? []
            ], JSON_PRETTY_PRINT));
            $report->logs_path = $path;
        }

        if (!empty($redacted['logs']['network'])) {
            $path = "{$storageDir}/network.json";
            Storage::disk('local')->put($path, json_encode($redacted['logs']['network'], JSON_PRETTY_PRINT));
            $report->network_path = $path;
        }

        if (!empty($redacted['logs']['breadcrumbs'])) {
            $path = "{$storageDir}/breadcrumbs.json";
            Storage::disk('local')->put($path, json_encode($redacted['logs']['breadcrumbs'], JSON_PRETTY_PRINT));
            $report->breadcrumbs_path = $path;
        }

        // Clicks jsou nyní v breadcrumbs nebo meta, pokud jsou povoleny
        if (!empty($redacted['clicks'])) {
             $path = "{$storageDir}/clicks.json";
             Storage::disk('local')->put($path, json_encode($redacted['clicks'], JSON_PRETTY_PRINT));
             $report->clicks_path = $path;
        }

        if (!empty($redacted['performance'])) {
            $path = "{$storageDir}/performance.json";
            Storage::disk('local')->put($path, json_encode($redacted['performance'], JSON_PRETTY_PRINT));
            $report->performance_path = $path;
        }

        $report->save();

        // Cache duplicate entry
        Cache::put($cacheKey, true, now()->addMinutes(config('feedback.limits.duplicate_check_minutes', 5)));

        // 8. Notifikace
        if (config('feedback.notifications.mail')) {
            $branding = app(\App\Services\BrandingService::class)->getSettings();
            // Prioritně admin email z nastavení brandingu, pak z configu (který bere ENV ERROR_REPORT_EMAIL)
            $recipients = $branding['admin_contact']['email'] ?? config('feedback.recipients');

            if (!empty($recipients)) {
                // Podpora pro více adres oddělených čárkou
                $recipientList = is_string($recipients) ? array_map('trim', explode(',', $recipients)) : $recipients;
                Mail::to($recipientList)->send(new FeedbackReportNotification($report));
            }
        }

        return response()->json([
            'message' => 'Děkujeme! Vaše zpětná vazba byla úspěšně odeslána.',
            'id' => $report->id,
        ]);
    }

    public function screenshot(FeedbackReport $report): SymfonyResponse
    {
        // Kontrola oprávnění (zjednodušená na to, zda má uživatel přístup do adminu)
        // V produkci by tu byla kontrola na konkrétní permission spatie
        if (!auth()->user() || !auth()->user()->hasRole(['super_admin', 'admin', 'technician'])) {
            abort(403);
        }

        if (!$report->screenshot_path || !Storage::disk('local')->exists($report->screenshot_path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($report->screenshot_path));
    }

    public function serverScreenshot(Request $request): JsonResponse
    {
        // Strategy gate
        $strategy = config('feedback.screenshot.strategy', 'auto');
        $allow = in_array($strategy, ['auto', 'playwright'], true) && config('feedback.screenshot.playwright.enabled', true);
        if (!$allow) {
            return response()->json([
                'ok' => false,
                'message' => 'Server-side screenshot disabled',
            ], 503);
        }

        $validated = $request->validate([
            'dom' => 'required|string',
            'head' => 'nullable|string',
            'viewport' => 'nullable|array',
            'viewport.width' => 'nullable|integer|min:320|max:3840',
            'viewport.height' => 'nullable|integer|min:240|max:2160',
            'dpr' => 'nullable|numeric|min:1|max:3',
            'selector' => 'nullable|string',
            'fullPage' => 'nullable|boolean',
            'bodyClass' => 'nullable|string',
            'bodyStyle' => 'nullable|string',
            'htmlClass' => 'nullable|string',
        ]);

        try {
            $svc = app(\App\Services\ScreenshotService::class);
            $result = $svc->captureViaPlaywrightFromDom($validated['dom'], [
                'viewport' => $validated['viewport'] ?? ['width' => 1728, 'height' => 919],
                'dpr' => $validated['dpr'] ?? 2,
                'selector' => $validated['selector'] ?? '#snapshot-root',
                'fullPage' => $validated['fullPage'] ?? false,
                'context' => [
                    'user_id' => $request->user()?->id,
                    'head' => $validated['head'] ?? '',
                    'body_class' => $validated['bodyClass'] ?? '',
                    'body_style' => $validated['bodyStyle'] ?? '',
                    'html_class' => $validated['htmlClass'] ?? '',
                ],
            ]);

            return response()->json([
                'ok' => true,
                'image' => $result['data_url'] ?? null,
                'width' => $result['width'],
                'height' => $result['height'],
                'mime' => $result['mime'] ?? 'image/png',
            ]);
        } catch (\Throwable $e) {
            \Log::error('Server screenshot failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'ok' => false,
                'message' => 'Server screenshot failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    protected function redactData(array $data): array
    {
        $redactKeys = config('feedback.redaction.redact_keys', []);
        $redactPatterns = config('feedback.redaction.redact_patterns', []);

        array_walk_recursive($data, function (&$value, $key) use ($redactKeys, $redactPatterns) {
            if (is_string($value)) {
                // Redact by key name
                if (in_array(strtolower((string)$key), $redactKeys)) {
                    $value = '[REDACTED]';
                    return;
                }

                // Redact by patterns
                foreach ($redactPatterns as $pattern) {
                    $value = preg_replace($pattern, '[REDACTED]', $value);
                }
            }
        });

        return $data;
    }
}
