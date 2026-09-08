#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 VENDOR_SHA OUTPUT_ARCHIVE" >&2
    exit 64
fi

vendor_sha=$1
output_archive=$2

if [[ ! "$vendor_sha" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Vendor SHA must contain exactly 40 lowercase hexadecimal characters." >&2
    exit 64
fi
if [ ! -f vendor/autoload.php ]; then
    echo "Production Composer dependencies must exist before packaging." >&2
    exit 1
fi

compressor=(gzip -1)
if command -v pigz >/dev/null 2>&1; then
    compressor=(pigz -1)
fi

cleanup() {
    status=$?
    if [ "$status" -ne 0 ]; then
        rm -f "$output_archive"
    fi
    exit "$status"
}
trap cleanup EXIT

COPYFILE_DISABLE=1 tar --no-xattrs -cf - vendor | "${compressor[@]}" > "$output_archive"
echo "Packaged production vendor $vendor_sha into $output_archive"
