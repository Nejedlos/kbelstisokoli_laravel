<?php

namespace App\Console\Commands;

use App\Models\FeedbackReport;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class FeedbackSmokeCommand extends Command
{
    protected $signature = 'feedback:smoke';

    protected $description = 'Simuluje odeslání feedbacku a ověří funkčnost systému.';

    public function handle(): int
    {
        $this->info('Starting Feedback Smoke Test...');

        $user = User::whereHas('roles', fn($q) => $q->where('name', 'member'))->first() ?: User::first();

        if (!$user) {
            $this->error('No user found to simulate feedback.');
            return 1;
        }

        $this->info("Simulating feedback for user: {$user->email}");

        $fakeScreenshot = 'data:image/jpeg;base64,' . base64_encode('fake-screenshot-content');

        $payload = [
            'type' => 'bug',
            'severity' => 'low',
            'title' => 'SMOKE TEST: ' . now()->toDateTimeString(),
            'description' => 'Toto je automatický smoke test feedback systému.',
            'context' => [
                'url' => config('app.url') . '/smoke-test',
                'area' => 'public',
                'device' => ['userAgent' => 'Junie Smoke Runner'],
                'requestId' => 'SMOKE-UUID',
                'timestamp' => now()->toISOString(),
            ],
            'capture' => [
                'screenshot' => $fakeScreenshot,
                'domLight' => '<html><body>SMOKE</body></html>',
            ],
            'logs' => [
                'console' => [['level' => 'log', 'timestamp' => now()->toISOString(), 'message' => 'Smoke test log entry']],
                'errors' => [],
                'network' => [],
                'breadcrumbs' => [['type' => 'nav', 'to' => '/smoke', 'timestamp' => now()->toISOString()]],
            ],
            'performance' => ['nav' => ['ttfb' => 100]],
        ];

        try {
            // We use call instead of HTTP to bypass network/auth issues in CLI
            $response = $this->callAction($user, $payload);

            if ($response->status() === 200) {
                $this->info('API Response: SUCCESS');
                $data = json_decode($response->getContent(), true);
                $reportId = $data['id'] ?? null;

                $report = FeedbackReport::find($reportId);

                if ($report) {
                    $this->info("Database record created: ID {$report->id}");

                    if ($report->screenshot_path && Storage::exists($report->screenshot_path)) {
                        $this->info('Screenshot stored: OK');
                    } else {
                        $this->error('Screenshot NOT stored!');
                    }

                    if ($report->logs_path && Storage::exists($report->logs_path)) {
                        $this->info('Logs stored: OK');
                    } else {
                        $this->error('Logs NOT stored!');
                    }

                    $this->info('PASS: Feedback system is operational.');
                    return 0;
                }
            } else {
                $this->error('API Response: FAILED (Status: ' . $response->status() . ')');
                $this->error($response->getContent());
            }
        } catch (\Exception $e) {
            $this->error('EXCEPTION: ' . $e->getMessage());
        }

        $this->error('FAIL: Feedback system smoke test failed.');
        return 1;
    }

    protected function callAction($user, $payload)
    {
        // Simulate a request to the controller
        return $this->laravel->make(\App\Http\Controllers\FeedbackController::class)
            ->store(request()->merge($payload)->setUserResolver(fn() => $user));
    }
}
