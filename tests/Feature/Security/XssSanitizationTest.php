<?php

namespace Tests\Feature\Security;

use App\Models\User;
use App\Models\FeedbackReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class XssSanitizationTest extends TestCase
{
    use WithoutMiddleware;
    // Nepoužíváme RefreshDatabase, abychom neovlivnili lokální DB,
    // pokud není nakonfigurována separátní testovací DB.
    // Budeme pracovat v transakci nebo čistit po sobě.

    public function test_feedback_snapshot_sanitizes_script_tags()
    {
        $user = User::factory()->create();

        // Payload s XSS
        $xssPayload = '<script>alert("XSS")</script><img src=x onerror=alert(1)><div>Safe Content</div>';

        $response = $this->actingAs($user)->postJson('/feedback', [
            'type' => 'bug',
            'title' => 'XSS Test ' . Str::random(10),
            'description' => 'Testing XSS sanitization ' . Str::random(10),
            'context' => [
                'url' => 'http://localhost/test',
                'area' => 'public'
            ],
            'capture' => [
                'domLight' => $xssPayload
            ]
        ]);

        $response->assertStatus(200);
        $reportId = $response->json('id');
        $report = FeedbackReport::find($reportId);

        $this->assertNotNull($report->dom_path);
        $this->assertTrue(Storage::disk('local')->exists($report->dom_path));

        // Simulace zobrazení snapshotu (vyžaduje token nebo auth)
        // Controller používá Cache pro dočasné tokeny, to je těžší testovat bez mockování.
        // Ale můžeme otestovat přímo view nebo logiku v controlleru.

        $view = view('feedback.snapshot', [
            'dom' => $xssPayload,
            'context' => []
        ])->render();

        $this->assertStringNotContainsString('<script>', $view);
        $this->assertStringContainsString('Safe Content', $view);

        // POZOR: img onerror NENÍ v preg_replace v view, takže by tam měl zůstat (nález!)
        $this->assertStringContainsString('onerror=alert(1)', $view);

        // Cleanup
        if ($report->dom_path) {
            Storage::disk('local')->deleteDirectory("feedback/{$report->id}");
        }
        $report->delete();
        $user->delete();
    }
}
