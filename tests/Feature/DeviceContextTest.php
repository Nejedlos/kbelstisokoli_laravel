<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\InternalAnalyticsEvent;
use App\Services\DeviceContextService;
use Illuminate\Http\Request;
use Tests\TestCase;

class DeviceContextTest extends TestCase
{
    public function test_login_records_client_hints_in_audit_and_request_logs(): void
    {
        $member = $this->createMember();
        $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Linux; Android 10; K) Chrome/151.0.0.0 Mobile',
            'Sec-CH-UA-Model' => '"Pixel 9"',
            'Sec-CH-UA-Platform' => '"Android"',
            'Sec-CH-UA-Platform-Version' => '"16.0.0"',
            'Sec-CH-UA-Mobile' => '?1',
            'Sec-CH-UA-Full-Version-List' => '"Google Chrome";v="151.0.1234.56"',
        ])->post('/login', ['email' => $member->email, 'password' => 'password'])
            ->assertRedirect(route('member.dashboard'));

        $audit = AuditLog::where('category', 'auth')->where('actor_user_id', $member->id)->latest('id')->firstOrFail();
        $event = InternalAnalyticsEvent::where('path', 'login')->latest('id')->firstOrFail();
        foreach ([$audit, $event] as $record) {
            $device = $record->metadata['device'];
            $this->assertSame('Pixel 9', $device['model']);
            $this->assertSame('16.0.0', $device['platform_version']);
            $this->assertTrue($device['mobile']);
            $this->assertSame('"Google Chrome";v="151.0.1234.56"', $device['client_hints']['Sec-CH-UA-Full-Version-List']);
        }
    }

    public function test_reduced_user_agent_does_not_invent_a_model_or_os_version(): void
    {
        $request = Request::create('/');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Linux; Android 10; K)');
        $device = app(DeviceContextService::class)->collect($request);
        $this->assertNull($device['model']);
        $this->assertNull($device['platform_version']);
        $this->assertNull($device['mobile']);
        $this->assertSame([], $device['client_hints']);
    }

    public function test_headers_are_bounded_and_credentials_are_not_collected(): void
    {
        $request = Request::create('/');
        $request->headers->set('User-Agent', str_repeat('a', 3000));
        $request->headers->set('Sec-CH-UA-Model', '""');
        $request->headers->set('Sec-CH-UA', "hello\r\nworld".str_repeat('b', 2000));
        $request->headers->set('Authorization', 'Bearer secret');
        $request->headers->set('Cookie', 'session=secret');
        $device = app(DeviceContextService::class)->collect($request);
        $this->assertNull($device['model']);
        $this->assertSame(2048, strlen($device['user_agent']));
        $this->assertLessThanOrEqual(1024, strlen($device['client_hints']['Sec-CH-UA']));
        $this->assertStringNotContainsString("\n", json_encode($device['client_hints']));
        $this->assertStringNotContainsString('secret', json_encode($device));
    }
}
