#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 RELEASE_SHA OUTPUT_ARCHIVE" >&2
    exit 64
fi

release_sha=$1
output_archive=$2

if [[ ! "$release_sha" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Release SHA must contain exactly 40 lowercase hexadecimal characters." >&2
    exit 64
fi

if [ ! -f vendor/autoload.php ] || [ ! -f public/build/manifest.json ]; then
    echo "Production dependencies and Vite assets must exist before packaging." >&2
    exit 1
fi

printf '%s\n' "$release_sha" > .release-sha
cleanup() {
    status=$?
    rm -f .release-sha
    if [ "$status" -ne 0 ]; then
        rm -f "$output_archive"
    fi
    exit "$status"
}
trap cleanup EXIT

compressor=(gzip -1)
if command -v pigz >/dev/null 2>&1; then
    compressor=(pigz -1)
fi

COPYFILE_DISABLE=1 tar --no-xattrs -cf - \
    --exclude='./.git' \
    --exclude='./.github' \
    --exclude='./.env' \
    --exclude='./.env.*' \
    --exclude='./*.env' \
    --exclude='./*.env.*' \
    --exclude='./*.log' \
    --exclude='./*.sql' \
    --exclude='./auth.json' \
    --exclude='./node_modules' \
    --exclude='./vendor' \
    --exclude='./tests' \
    --exclude='./docs' \
    --exclude='./storage' \
    --exclude='./resources/icons' \
    --exclude='./public/assets' \
    --exclude='./public/uploads' \
    --exclude='./public/storage' \
    --exclude='./public/hot' \
    --exclude='./.phpunit.cache' \
    --exclude='./.phpunit.result.cache' \
    . | "${compressor[@]}" > "$output_archive"

echo "Packaged release $release_sha into $output_archive"
