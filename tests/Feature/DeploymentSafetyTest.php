<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentSafetyTest extends TestCase
{
    public function test_github_deployment_only_builds_code_and_runs_migrations(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/lint.yml'));

        $this->assertStringContainsString('git remote add origin "https://github.com/Nejedlos/kbelstisokoli_laravel.git"', $workflow);
        $this->assertStringContainsString('"$npm_binary" run build', $workflow);
        $this->assertStringContainsString('php8.4 artisan migrate --force --no-interaction', $workflow);
        $this->assertStringNotContainsString('artisan app:sync', $workflow);
        $this->assertStringNotContainsString('artisan icons:cache', $workflow);
        $this->assertStringNotContainsString('artisan livewire:publish', $workflow);
        $this->assertStringNotContainsString('artisan system:cleanup', $workflow);
    }

    public function test_direct_deployment_does_not_import_or_delete_domain_data(): void
    {
        $envoy = file_get_contents(base_path('Envoy.blade.php'));
        preg_match("/@task\('deploy'.*?@endtask/s", $envoy, $matches);
        $deployment = $matches[0] ?? '';

        $this->assertStringContainsString('npm ci --no-audit --no-fund', $deployment);
        $this->assertStringContainsString('artisan migrate --force --no-interaction', $deployment);
        $this->assertStringNotContainsString('git clean', $deployment);
        $this->assertStringNotContainsString('artisan app:sync', $deployment);
        $this->assertStringNotContainsString('artisan ai:index', $deployment);
        $this->assertStringNotContainsString('artisan icons:cache', $deployment);
        $this->assertStringNotContainsString('env_contents', $deployment);
    }

    public function test_initial_setup_does_not_import_domain_data(): void
    {
        $envoy = file_get_contents(base_path('Envoy.blade.php'));
        preg_match("/@task\('setup'.*?@endtask/s", $envoy, $matches);
        $setup = $matches[0] ?? '';

        $this->assertStringContainsString('artisan migrate --force --no-interaction', $setup);
        $this->assertStringNotContainsString('git clean', $setup);
        $this->assertStringNotContainsString('artisan app:sync', $setup);
        $this->assertStringNotContainsString('artisan ai:index', $setup);
        $this->assertStringNotContainsString('artisan icons:cache', $setup);
    }
}
