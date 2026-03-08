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

class FeedbackController extends Controller
{
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
            'url' => 'required|string',
            'route_name' => 'nullable|string',
            'page_title' => 'nullable|string',
            'user_agent' => 'required|string',
            'viewport' => 'nullable|array',
            'screen' => 'nullable|array',
            'timezone' => 'nullable|string',
            'source_area' => 'required|in:public,member,admin',
            'screenshot' => 'nullable|string', // Base64
            'logs' => 'nullable|array',
            'network' => 'nullable|array',
            'clicks' => 'nullable|array',
            'meta' => 'nullable|array',
        ]);

        // 4. Payload size guard (rough check)
        $payloadSize = strlen(serialize($request->all()));
        if ($payloadSize > config('feedback.limits.max_payload_bytes', 6291456)) {
             return response()->json([
                'message' => 'Payload je příliš velký. Zkuste prosím vypnout přiložení screenshotu nebo logů.',
            ], 413);
        }

        // 5. Redaction
        $validated = $this->redactData($validated);

        // 6. Uložení Reportu
        $report = new FeedbackReport();
        $report->user_id = $user->id;
        $report->type = $validated['type'];
        $report->severity = $validated['severity'] ?? null;
        $report->title = $validated['title'];
        $report->description = $validated['description'];
        $report->steps = $validated['steps'] ?? null;
        $report->url = $validated['url'];
        $report->route_name = $validated['route_name'] ?? null;
        $report->locale = app()->getLocale();
        $report->user_agent = $validated['user_agent'];
        $report->viewport = $validated['viewport'] ?? null;
        $report->screen = $validated['screen'] ?? null;
        $report->timezone = $validated['timezone'] ?? null;
        $report->source_area = $validated['source_area'];
        $report->app_version = AppVersion::get();
        $report->ip = $request->ip();
        $report->meta = array_merge($validated['meta'] ?? [], [
            'page_title' => $validated['page_title'] ?? null,
            'user_email' => $user->email,
            'user_roles' => $user->getRoleNames(),
        ]);
        $report->save();

        // 7. Uložení souborů
        $storageDir = "feedback/{$report->id}";

        if (!empty($validated['screenshot'])) {
            $screenshotData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $validated['screenshot']));
            $path = "{$storageDir}/screenshot.jpg";
            Storage::put($path, $screenshotData);
            $report->screenshot_path = $path;
        }

        if (!empty($validated['logs'])) {
            $path = "{$storageDir}/logs.json";
            Storage::put($path, json_encode($validated['logs'], JSON_PRETTY_PRINT));
            $report->logs_path = $path;
        }

        if (!empty($validated['network'])) {
            $path = "{$storageDir}/network.json";
            Storage::put($path, json_encode($validated['network'], JSON_PRETTY_PRINT));
            $report->network_path = $path;
        }

        if (!empty($validated['clicks'])) {
            $path = "{$storageDir}/clicks.json";
            Storage::put($path, json_encode($validated['clicks'], JSON_PRETTY_PRINT));
            $report->clicks_path = $path;
        }

        $report->save();

        // Cache duplicate entry
        Cache::put($cacheKey, true, now()->addMinutes(config('feedback.limits.duplicate_check_minutes', 5)));

        // 8. Notifikace
        if (config('feedback.notifications.mail')) {
            $recipients = config('feedback.recipients');
            if (!empty($recipients)) {
                Mail::to($recipients)->send(new FeedbackReportNotification($report));
            }
        }

        return response()->json([
            'message' => 'Děkujeme! Vaše zpětná vazba byla úspěšně odeslána.',
            'id' => $report->id,
        ]);
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
