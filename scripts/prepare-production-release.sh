#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -ne 6 ]; then
    echo "Usage: $0 RELEASE_SHA RELEASE_ARCHIVE VENDOR_SHA VENDOR_ARCHIVE ASSETS_SHA ASSETS_ARCHIVE" >&2
    exit 64
fi

release_sha=$1
release_archive=$2
vendor_sha=$3
vendor_archive=$4
assets_sha=$5
assets_archive=$6
repo_root=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$repo_root"

if [[ ! "$release_sha" =~ ^[0-9a-f]{40}$ ]] || [[ ! "$vendor_sha" =~ ^[0-9a-f]{40}$ ]] || [[ ! "$assets_sha" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Release, vendor and assets identifiers must be 40-character lowercase hashes." >&2
    exit 64
fi
if [ "$(git rev-parse HEAD)" != "$release_sha" ]; then
    echo "Release SHA must exactly match the checked-out commit." >&2
    exit 1
fi
if [ "$(git rev-parse "$release_sha:public/assets")" != "$assets_sha" ]; then
    echo "Assets SHA does not match public/assets in the release commit." >&2
    exit 1
fi
if [ ! -f public/build/manifest.json ]; then
    echo "Production Vite assets must exist before preparing the release." >&2
    exit 1
fi

temporary_parent=${RUNNER_TEMP:-${TMPDIR:-/tmp}}
temporary_root=$(mktemp -d "$temporary_parent/kbelstisokoli-release.XXXXXX")
vendor_candidate=
cleanup() {
    status=$?
    rm -rf "$temporary_root"
    if [ -n "$vendor_candidate" ]; then
        rm -rf "$vendor_candidate"
    fi
    if [ "$status" -ne 0 ]; then
        rm -f "$release_archive" "$vendor_archive" "$assets_archive"
    fi
    exit "$status"
}
trap cleanup EXIT

source_root="$temporary_root/source"
mkdir -p "$source_root"
git archive "$release_sha" | tar -x -C "$source_root"
rm -rf "$source_root/public/build"
COPYFILE_DISABLE=1 cp -a public/build "$source_root/public/build"
mkdir -p "$source_root/bootstrap/cache" "$source_root/storage/framework"/{cache/data,sessions,testing,views}

if [ -n "${DEPLOY_CACHE_ROOT:-}" ]; then
    cache_root=$DEPLOY_CACHE_ROOT
elif [ -n "${RUNNER_WORKSPACE:-}" ]; then
    cache_root="$RUNNER_WORKSPACE/.deployment-cache/kbelstisokoli"
else
    cache_root="${TMPDIR:-/tmp}/kbelstisokoli-deployment-cache"
fi
mkdir -p "$cache_root"

expected_vendor_sha=$(scripts/production-vendor-id.sh)
if [ "$vendor_sha" != "$expected_vendor_sha" ]; then
    echo "Vendor SHA does not match the current Composer platform and lock files." >&2
    exit 1
fi
vendor_cache="$cache_root/vendor-$vendor_sha"
vendor_archive_cache="$cache_root/vendor-$vendor_sha.tgz"

if [ ! -f "$vendor_cache/vendor/autoload.php" ]; then
    vendor_candidate=$(mktemp -d "$cache_root/.vendor-$vendor_sha.XXXXXX")
    if [ -f "$repo_root/vendor/autoload.php" ]; then
        if ! COPYFILE_DISABLE=1 cp -cR "$repo_root/vendor" "$source_root/vendor" 2>/dev/null; then
            rm -rf "$source_root/vendor"
            COPYFILE_DISABLE=1 cp -a "$repo_root/vendor" "$source_root/vendor"
        fi
    fi
    composer install --working-dir="$source_root" \
        --no-dev --no-progress --prefer-dist --optimize-autoloader --no-scripts
    if ! COPYFILE_DISABLE=1 cp -cR "$source_root/vendor" "$vendor_candidate/vendor" 2>/dev/null; then
        rm -rf "$vendor_candidate/vendor"
        COPYFILE_DISABLE=1 cp -a "$source_root/vendor" "$vendor_candidate/vendor"
    fi
    rm -rf "$vendor_cache"
    mv "$vendor_candidate" "$vendor_cache"
    vendor_candidate=
    echo "Created production Composer cache $vendor_cache"
else
    echo "Using production Composer cache $vendor_cache"
    if ! COPYFILE_DISABLE=1 cp -cR "$vendor_cache/vendor" "$source_root/vendor" 2>/dev/null; then
        rm -rf "$source_root/vendor"
        COPYFILE_DISABLE=1 cp -a "$vendor_cache/vendor" "$source_root/vendor"
    fi
fi
if [ ! -f "$vendor_archive_cache" ]; then
    (
        cd "$source_root"
        scripts/package-production-vendor.sh "$vendor_sha" "$vendor_archive_cache"
    )
fi
COPYFILE_DISABLE=1 cp -a "$vendor_archive_cache" "$vendor_archive"
(
    cd "$source_root"
    php artisan package:discover --ansi
    php artisan filament:upgrade
    scripts/package-production-release.sh "$release_sha" "$release_archive"
)
scripts/package-production-assets.sh "$assets_sha" "$assets_archive"
