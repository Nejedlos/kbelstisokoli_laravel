<?php

namespace Database\Factories;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'occurred_at' => now(),
            'category' => 'system',
            'event_key' => 'test_event',
            'action' => 'test_action',
            'severity' => 'info',
            'actor_type' => 'user',
            'ip_address' => $this->faker->ipv4(),
            'user_agent_summary' => $this->faker->userAgent(),
            'is_system_event' => false,
            'source' => 'web',
        ];
    }
}
