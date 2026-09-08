<?php

namespace Tests\Feature;

use Tests\TestCase;

class DeploymentSafetyTest extends TestCase
{
    public function test_github_deployment_builds_one_immutable_release_and_promotes_it(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/lint.yml'));

        $this->assertStringContainsString('php artisan test --parallel --processes=4 --compact', $workflow);
        $this->assertSame(1, substr_count($workflow, 'npm run build'));
        $this->assertStringContainsString('PRODUCTION_PUBLIC_PATH: ${{ secrets.PRODUCTION_PUBLIC_PATH }}', $workflow);
        $this->assertStringContainsString('scripts/package-production-release.sh "$GITHUB_SHA"', $workflow);
        $this->assertStringContainsString('scripts/package-production-assets.sh "$assets_sha"', $workflow);
        $this->assertStringContainsString('rm -f bootstrap/cache/*.php', $workflow);
        $this->assertStringContainsString('scripts/upload-production-release.sh', $workflow);
        $this->assertStringNotContainsString('rsync ', $workflow);
        $this->assertStringNotContainsString('git reset --hard', $workflow);
        $this->assertStringNotContainsString('git fetch', $workflow);
        $this->assertStringNotContainsString('npm_binary', $workflow);
        $this->assertStringNotContainsString('artisan optimize:clear', $workflow);
        $this->assertStringNotContainsString('artisan app:sync', $workflow);
        $this->assertStringNotContainsString('artisan icons:cache', $workflow);
        $this->assertStringNotContainsString('artisan livewire:publish', $workflow);
        $this->assertStringNotContainsString('artisan system:cleanup', $workflow);

        $packaging = file_get_contents(base_path('scripts/package-production-release.sh'));
        $this->assertStringContainsString('| gzip -1 > "$output_archive"', $packaging);
        $this->assertStringContainsString("--exclude='./resources/icons'", $packaging);
        $this->assertStringContainsString("--exclude='./public/assets'", $packaging);

        $upload = file_get_contents(base_path('scripts/upload-production-release.sh'));
        $this->assertStringContainsString('deploy-production-release.sh', $upload);
        $this->assertStringContainsString('are already present on production.', $upload);
        $this->assertStringContainsString('ConnectTimeout=20', $upload);
        $this->assertStringContainsString('if [ "$status" -ne 255 ]; then', $upload);
        $this->assertStringContainsString('remote_release_checksum', $upload);
        $this->assertStringContainsString('StrictHostKeyChecking=yes', $upload);

        $localFallback = file_get_contents(base_path('scripts/deploy-production-from-local.sh'));
        $this->assertStringContainsString('Local fallback deploy requires a clean working tree.', $localFallback);
        $this->assertStringContainsString('Local main must exactly match origin/main.', $localFallback);
        $this->assertStringContainsString('scripts/upload-production-release.sh', $localFallback);
    }

    public function test_release_promotion_preserves_persistent_paths_and_has_rollback_guards(): void
    {
        $deployment = file_get_contents(base_path('scripts/deploy-production-release.sh'));

        $this->assertStringContainsString('flock -w 300', $deployment);
        $this->assertStringContainsString('sha256sum -c -', $deployment);
        $this->assertStringContainsString('ln -s "$PRODUCTION_PATH/storage" "$temporary_release/storage"', $deployment);
        $this->assertStringContainsString('ln -s "$PRODUCTION_PATH/resources/icons" "$temporary_release/resources/icons"', $deployment);
        $this->assertStringContainsString('ln -s "$PRODUCTION_PUBLIC_PATH/uploads" "$temporary_release/public/uploads"', $deployment);
        $this->assertStringContainsString('ln -s "$PRODUCTION_PUBLIC_PATH/storage" "$temporary_release/public/storage"', $deployment);
        $this->assertStringContainsString('ln -s "$managed_assets_path" "$temporary_release/public/assets"', $deployment);
        $this->assertStringContainsString('ln -s "$shared_partners" "$extracted_assets/img/partners"', $deployment);
        $this->assertStringContainsString('down --retry=5 --no-interaction', $deployment);
        $this->assertStringContainsString('resume_after_partner_move', $deployment);
        $this->assertStringContainsString('mv "$partner_source_path" "$shared_partners"', $deployment);
        $this->assertStringContainsString('Timed out while waiting for active PHP requests to finish.', $deployment);
        $this->assertStringContainsString('favicon.ico', $deployment);
        $this->assertStringContainsString('robots.txt', $deployment);
        $this->assertStringContainsString('site.webmanifest', $deployment);
        $this->assertStringContainsString('mv -Tf "$deploy_root/.current-$RELEASE_SHA" "$current_link"', $deployment);
        $this->assertStringContainsString('rollback_bootstrap', $deployment);
        $this->assertStringContainsString('X-App-Release: $RELEASE_SHA', $deployment);
        $this->assertGreaterThan(
            strpos($deployment, 'mv "$temporary_release" "$release_path"'),
            strpos($deployment, '"$php_binary" "$release_path/artisan" optimize'),
        );
        $this->assertStringContainsString('for health_path in /up / /treninky /akce /zapasy /tymy', $deployment);
        $this->assertStringContainsString('&& [ "$current_switched" != true ]', $deployment);
        $this->assertStringNotContainsString('rm -rf "$PRODUCTION_PUBLIC_PATH', $deployment);
        $this->assertStringNotContainsString('cache:clear', $deployment);
    }

    public function test_production_front_controller_resolves_only_a_managed_release(): void
    {
        $index = file_get_contents(base_path('public/index.production.php'));

        $this->assertStringContainsString("realpath(\$deployRoot.'/current')", $index);
        $this->assertStringContainsString("str_starts_with(\$appBase.'/', \$releasesRoot)", $index);
        $this->assertStringContainsString("header('X-App-Release: '.\$releaseId)", $index);
        $this->assertStringContainsString("cache-full_page_'.\$releaseId.'_", $index);
        $this->assertStringContainsString("define('LARAVEL_RELEASE_PUBLIC_PATH', \$appBase.'/public')", $index);
        $this->assertStringNotContainsString('public_html/secret/vendor/autoload.php', $index);

        $maintenancePosition = strpos($index, 'storage/framework/maintenance.php');
        $fastCachePosition = strpos($index, '// Fast cache is namespaced by release.');
        $this->assertIsInt($maintenancePosition);
        $this->assertIsInt($fastCachePosition);
        $this->assertLessThan($fastCachePosition, $maintenancePosition);

        $bootstrap = file_get_contents(base_path('bootstrap/app.php'));
        $this->assertStringContainsString("defined('LARAVEL_RELEASE_PUBLIC_PATH')", $bootstrap);
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
