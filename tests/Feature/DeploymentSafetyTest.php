<?php

namespace Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\TestCase;

class DeploymentSafetyTest extends TestCase
{
    public function test_github_deployment_builds_one_immutable_release_and_promotes_it(): void
    {
        $workflow = file_get_contents(base_path('.github/workflows/lint.yml'));

        $this->assertStringContainsString('php artisan test --parallel --processes=4 --compact', $workflow);
        $this->assertStringContainsString('runs-on: [self-hosted, macOS, ARM64, kbelstisokoli-deploy]', $workflow);
        $this->assertStringContainsString('clean: false', $workflow);
        $this->assertStringContainsString('timeout-minutes: 20', $workflow);
        $this->assertStringContainsString('Verify self-hosted toolchain', $workflow);
        $this->assertStringContainsString('version_compare(PHP_VERSION, "8.4.0", "<")', $workflow);
        $this->assertStringContainsString('PHP memory_limit must be at least 512M for parallel tests.', $workflow);
        $this->assertStringContainsString('process.versions.node', $workflow);
        $this->assertStringContainsString('pigz --version', $workflow);
        $this->assertSame(1, substr_count($workflow, 'npm run build'));
        $this->assertStringContainsString('scripts/prepare-production-node-modules.sh', $workflow);
        $this->assertStringContainsString('PRODUCTION_PUBLIC_PATH: ${{ secrets.PRODUCTION_PUBLIC_PATH }}', $workflow);
        $this->assertStringContainsString('scripts/prepare-production-release.sh', $workflow);
        $this->assertStringContainsString('scripts/production-vendor-id.sh', $workflow);
        $this->assertStringContainsString('"$VENDOR_SHA" "$RUNNER_TEMP/vendor-$VENDOR_SHA.tgz"', $workflow);
        $this->assertStringContainsString('scripts/upload-production-release.sh', $workflow);
        $this->assertStringContainsString('$RUNNER_TEMP/deploy-ssh', $workflow);
        $this->assertStringNotContainsString('> ~/.ssh/id_ed25519', $workflow);
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
        $this->assertStringContainsString('compressor=(gzip -1)', $packaging);
        $this->assertStringContainsString('compressor=(pigz -1)', $packaging);
        $this->assertStringContainsString('| "${compressor[@]}" > "$output_archive"', $packaging);
        $this->assertStringContainsString('COPYFILE_DISABLE=1 tar --no-xattrs', $packaging);
        $this->assertStringContainsString("--exclude='./resources/icons'", $packaging);
        $this->assertStringContainsString("--exclude='./public/assets'", $packaging);
        $this->assertStringContainsString("--exclude='./vendor'", $packaging);

        $preparation = file_get_contents(base_path('scripts/prepare-production-release.sh'));
        $this->assertStringContainsString('git archive "$release_sha"', $preparation);
        $this->assertStringContainsString('DEPLOY_CACHE_ROOT', $preparation);
        $this->assertStringContainsString('vendor-$vendor_sha.tgz', $preparation);
        $this->assertStringContainsString('composer install --working-dir="$source_root"', $preparation);
        $this->assertStringContainsString('scripts/package-production-vendor.sh', $preparation);
        $this->assertStringContainsString('scripts/package-production-release.sh', $preparation);

        $upload = file_get_contents(base_path('scripts/upload-production-release.sh'));
        $this->assertStringContainsString('deploy-production-release.sh', $upload);
        $this->assertStringContainsString('are already present on production.', $upload);
        $this->assertStringContainsString('ConnectTimeout=20', $upload);
        $this->assertStringContainsString('if [ "$status" -ne 255 ]; then', $upload);
        $this->assertStringContainsString('remote_release_checksum', $upload);
        $this->assertStringContainsString('StrictHostKeyChecking=yes', $upload);
        $this->assertStringContainsString('IdentitiesOnly=yes', $upload);
        $this->assertStringContainsString('UserKnownHostsFile=', $upload);
        $this->assertStringContainsString('managed-vendor', $upload);
        $this->assertStringContainsString('VENDOR_CHECKSUM=', $upload);
        $this->assertStringContainsString('remote_bash', $upload);
        $this->assertStringContainsString('remote_bash_once', $upload);
        $this->assertStringContainsString('incoming/$release_sha', $upload);
        $this->assertStringContainsString('bash -lc', $upload);
        $this->assertStringNotContainsString('then exit 254', $upload);

        $localFallback = file_get_contents(base_path('scripts/deploy-production-from-local.sh'));
        $this->assertStringContainsString('Local fallback deploy requires a clean working tree.', $localFallback);
        $this->assertStringContainsString('Local main must exactly match origin/main.', $localFallback);
        $this->assertStringContainsString('scripts/prepare-production-release.sh', $localFallback);
        $this->assertStringContainsString('scripts/upload-production-release.sh', $localFallback);
        $this->assertStringContainsString('scripts/prepare-production-node-modules.sh', $localFallback);

        $nodeDependencies = file_get_contents(base_path('scripts/prepare-production-node-modules.sh'));
        $this->assertStringContainsString('FONTAWESOME_TOKEN', $nodeDependencies);
        $this->assertStringContainsString('security find-generic-password', $nodeDependencies);
        $this->assertStringContainsString('FORCE_NPM_CI', $nodeDependencies);
        $this->assertStringContainsString('package-lock.json', $nodeDependencies);
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
        $this->assertStringContainsString('cp -al "$managed_vendor_path/." "$temporary_release/vendor/"', $deployment);
        $this->assertStringContainsString('Managed production vendor has an unexpected marker.', $deployment);
        $this->assertStringContainsString('diff -qr "$current_link/database/migrations" "$release_path/database/migrations"', $deployment);
        $this->assertStringContainsString('Migration files are unchanged; skipping migration check.', $deployment);
        $this->assertStringContainsString('migrate --force --no-interaction', $deployment);
        $this->assertStringContainsString('ln -s "$shared_partners" "$extracted_assets/img/partners"', $deployment);
        $this->assertStringContainsString('down --retry=5 --no-interaction', $deployment);
        $this->assertStringContainsString('resume_after_partner_move', $deployment);
        $this->assertStringContainsString('mv "$partner_source_path" "$shared_partners"', $deployment);
        $this->assertStringContainsString('Timed out while waiting for active PHP requests to finish.', $deployment);
        $this->assertStringContainsString('favicon.ico', $deployment);
        $this->assertStringContainsString('robots.txt', $deployment);
        $this->assertStringContainsString('site.webmanifest', $deployment);
        $this->assertStringNotContainsString('assets build js css fonts vendor images', $deployment);
        $this->assertStringContainsString('mv -Tf "$deploy_root/.current-$RELEASE_SHA" "$current_link"', $deployment);
        $this->assertStringContainsString('rollback_bootstrap', $deployment);
        $this->assertStringContainsString('awk -v expected="$RELEASE_SHA"', $deployment);
        $this->assertGreaterThan(
            strpos($deployment, 'mv "$temporary_release" "$release_path"'),
            strpos($deployment, '"$php_binary" "$release_path/artisan" optimize'),
        );
        $this->assertStringContainsString('optimize --except=blade-icons --no-interaction', $deployment);
        $this->assertStringNotContainsString('"$php_binary" "$release_path/artisan" filament:optimize', $deployment);
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

    public static function healthScenarios(): array
    {
        return [
            'ready' => ['ready', true, 6],
            'old worker recovers' => ['transient', true, 7],
            'last endpoint forces complete new sweep' => ['last_endpoint', true, 12],
            '120 second cache expires' => ['cache_ttl', true, 30],
            'persistent old release' => ['wrong_release', false, 30],
            'redirect with correct header' => ['redirect', false, 30],
            'duplicate release header' => ['duplicate', false, 30],
            'extra text in release header' => ['malformed', false, 30],
            'missing release header' => ['missing', false, 30],
            'network timeouts exhaust remaining budget' => ['timeout', false, 5],
        ];
    }

    #[DataProvider('healthScenarios')]
    public function test_health_sweep_recovers_or_rolls_back_within_a_bounded_deadline(string $scenario, bool $success, int $expectedCalls): void
    {
        $deployment = file_get_contents(base_path('scripts/deploy-production-release.sh'));
        preg_match('/^verify_release_health\(\) \{\n.*?^\}/ms', $deployment, $healthFunction);
        preg_match('/^rollback_current\(\) \{\n.*?^\}/ms', $deployment, $rollbackFunction);
        $this->assertNotEmpty($healthFunction);
        $this->assertNotEmpty($rollbackFunction);

        $directory = sys_get_temp_dir().'/sokoli-health-'.bin2hex(random_bytes(8));
        mkdir($directory.'/releases/old', 0777, true);
        mkdir($directory.'/releases/new');
        symlink('releases/new', $directory.'/current');
        file_put_contents($directory.'/clock', '0');
        file_put_contents($directory.'/calls', '0');

        // Execute the real shell health and rollback functions. Only time, HTTP
        // and GNU mv's atomic rename are replaced by deterministic local doubles.
        $harness = <<<'BASH'
set -euo pipefail
deploy_root=$TEST_DIRECTORY
current_link="$deploy_root/current"
old_target=releases/old
current_switched=true
headers_file="$deploy_root/headers"
health_base=https://example.invalid
curl_binary=mock_curl
date() { cat "$deploy_root/clock"; }
sleep() {
    printf '%s\n' "$1" >> "$deploy_root/sleeps"
    printf '%s' "$(( $(date +%s) + $1 ))" > "$deploy_root/clock"
}
mv() {
    [ "$1" = '-Tf' ]
    "$TEST_PHP_BINARY" -r 'exit(rename($argv[1], $argv[2]) ? 0 : 1);' "$2" "$3"
}
mock_curl() {
    local headers='' maximum='' url='' cache_control='' writer=''
    while [ "$#" -gt 0 ]; do
        case "$1" in
            -D) headers=$2; shift 2 ;;
            --max-time) maximum=$2; shift 2 ;;
            -H) cache_control=$2; shift 2 ;;
            -w) writer=$2; shift 2 ;;
            -o) shift 2 ;;
            -fsS) shift ;;
            *) url=$1; shift ;;
        esac
    done
    [ "$cache_control" = 'Cache-Control: no-cache' ] || return 90
    [ "$writer" = '%{http_code}' ] || return 91
    local count now status=200 release=$RELEASE_SHA
    count=$(( $(cat "$deploy_root/calls") + 1 ))
    now=$(date +%s)
    printf '%s' "$count" > "$deploy_root/calls"
    printf '%s|%s|%s\n' "$now" "$maximum" "$url" >> "$deploy_root/requests"
    case "$TEST_SCENARIO" in
        transient) if [ "$count" -eq 1 ]; then release=old; fi ;;
        last_endpoint) if [ "$count" -eq 6 ]; then release=old; fi ;;
        cache_ttl) if [ "$now" -lt 120 ]; then release=old; fi ;;
        wrong_release) release=old ;;
        redirect) status=302 ;;
        malformed) release="$RELEASE_SHA garbage" ;;
        timeout)
            printf '%s' "$((now + maximum))" > "$deploy_root/clock"
            return 28
            ;;
    esac
    printf 'HTTP/2 %s\r\n' "$status" > "$headers"
    if [ "$TEST_SCENARIO" != missing ]; then
        printf 'x-App-Release: %s\r\n' "$release" >> "$headers"
    fi
    if [ "$TEST_SCENARIO" = duplicate ]; then
        printf 'X-App-Release: \r\n' >> "$headers"
    fi
    printf '%s' "$status"
}
BASH;
        $harness .= "\n".$rollbackFunction[0]."\n".$healthFunction[0]."\n";
        $harness .= <<<'BASH'
trap 'status=$?; if [ "$status" -ne 0 ]; then rollback_current; fi; exit "$status"' EXIT
verify_release_health
BASH;

        try {
            $process = new Process(['bash', '-c', $harness], null, [
                'TEST_DIRECTORY' => $directory,
                'TEST_SCENARIO' => $scenario,
                'TEST_PHP_BINARY' => PHP_BINARY,
                'RELEASE_SHA' => str_repeat('a', 40),
            ]);
            $process->setTimeout(15)->run();
            $this->assertSame($success ? 0 : 1, $process->getExitCode(), $process->getErrorOutput());
            clearstatcache(true, $directory.'/current');
            $this->assertSame($success ? 'releases/new' : 'releases/old', readlink($directory.'/current'));
            $this->assertSame($expectedCalls, (int) file_get_contents($directory.'/calls'));
            $elapsed = (int) file_get_contents($directory.'/clock');
            $this->assertLessThanOrEqual(150, $elapsed);
            if (! $success) {
                $this->assertSame(150, $elapsed);
                $this->assertStringContainsString('current will be restored', $process->getErrorOutput());
            }
            $requests = file($directory.'/requests', FILE_IGNORE_NEW_LINES);
            foreach ($requests as $request) {
                [$started, $timeout, $url] = explode('|', $request);
                $this->assertGreaterThan(0, (int) $timeout);
                $this->assertLessThanOrEqual(min(30, 150 - (int) $started), (int) $timeout);
                $this->assertStringContainsString('?release='.str_repeat('a', 40).'&health_attempt=', $url);
                $this->assertMatchesRegularExpression('/&health_run=0-[1-9][0-9]*$/', $url);
            }
            if ($expectedCalls > 6 || ! $success) {
                $this->assertFileExists($directory.'/sleeps');
                $this->assertGreaterThan(0, array_sum(file($directory.'/sleeps')));
            }
            if ($success) {
                $finalUrls = array_map(fn (string $request): string => explode('|', $request)[2], array_slice($requests, -6));
                $this->assertSame(['/up', '/', '/treninky', '/akce', '/zapasy', '/tymy'], array_map(fn (string $url): string => parse_url($url, PHP_URL_PATH), $finalUrls));
                $this->assertCount(1, array_unique(array_map(fn (string $url): string => parse_url($url, PHP_URL_QUERY), $finalUrls)));
            }
        } finally {
            (new Filesystem)->deleteDirectory($directory);
        }
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
