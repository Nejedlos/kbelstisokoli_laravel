#!/usr/bin/env bash
set -euo pipefail

if [ "${1:-}" = "--ci-validated" ]; then
    ci_validated=true
    shift
else
    ci_validated=false
fi

if [ "$#" -ne 0 ]; then
    echo "Usage: $0 [--ci-validated]" >&2
    exit 64
fi

repo_root=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$repo_root"

if [ "$(git branch --show-current)" != main ]; then
    echo "Local fallback deploy is allowed only from main." >&2
    exit 1
fi
if ! git diff --quiet || ! git diff --cached --quiet || [ -n "$(git status --short --untracked-files=normal)" ]; then
    echo "Local fallback deploy requires a clean working tree." >&2
    exit 1
fi

git fetch --quiet origin main
release_sha=$(git rev-parse HEAD)
if [ "$release_sha" != "$(git rev-parse origin/main)" ]; then
    echo "Local main must exactly match origin/main." >&2
    exit 1
fi

for command_name in composer npm php ssh scp git tar curl; do
    if ! command -v "$command_name" >/dev/null 2>&1; then
        echo "Required local command is unavailable: $command_name" >&2
        exit 1
    fi
done

if [ "$ci_validated" != true ]; then
    mkdir -p bootstrap/cache storage/framework/{cache/data,sessions,testing,views}
    composer install --no-progress --prefer-dist
    vendor/bin/pint --test
    php artisan config:clear --ansi
    php artisan test --parallel --processes=4 --compact
fi

scripts/prepare-production-node-modules.sh
npm run build

if ! git diff --quiet || ! git diff --cached --quiet || [ -n "$(git status --short --untracked-files=normal)" ]; then
    echo "Validation or build changed the tracked working tree; refusing production upload." >&2
    exit 1
fi

temporary_root=$(mktemp -d "${TMPDIR:-/tmp}/kbelstisokoli-deploy.XXXXXX")
cleanup() {
    status=$?
    rm -rf "$temporary_root"
    exit "$status"
}
trap cleanup EXIT

release_archive="$temporary_root/release-$release_sha.tgz"
vendor_sha=$(scripts/production-vendor-id.sh)
vendor_archive="$temporary_root/vendor-$vendor_sha.tgz"
assets_sha=$(git rev-parse HEAD:public/assets)
assets_archive="$temporary_root/assets-$assets_sha.tar"
scripts/prepare-production-release.sh \
    "$release_sha" "$release_archive" \
    "$vendor_sha" "$vendor_archive" \
    "$assets_sha" "$assets_archive"

export PRODUCTION_SSH_HOST=${PRODUCTION_SSH_HOST:-dw191.webglobe.com}
export PRODUCTION_SSH_PORT=${PRODUCTION_SSH_PORT:-20001}
export PRODUCTION_SSH_USER=${PRODUCTION_SSH_USER:-ssh-588875}
export PRODUCTION_PATH=${PRODUCTION_PATH:-/home/html/kbelstisokoli.cz/public_html/secret}
export PRODUCTION_PUBLIC_PATH=${PRODUCTION_PUBLIC_PATH:-/home/html/kbelstisokoli.cz/public_html/www}
export HEALTH_URL=${HEALTH_URL:-https://kbelstisokoli.cz}
export PHP_BINARY=${PHP_BINARY:-php8.4}

scripts/upload-production-release.sh \
    "$release_sha" "$release_archive" \
    "$vendor_sha" "$vendor_archive" \
    "$assets_sha" "$assets_archive"

live_release=$(curl -fsSI "$HEALTH_URL/up" | tr -d '\r' | awk -F ': ' 'tolower($1) == "x-app-release" { print $2 }' | tail -n 1)
if [ "$live_release" != "$release_sha" ]; then
    echo "Live release header does not match $release_sha." >&2
    exit 1
fi

echo "Production is serving release $release_sha."
