#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -ne 2 ]; then
    echo "Usage: $0 ASSETS_SHA OUTPUT_ARCHIVE" >&2
    exit 64
fi

assets_sha=$1
output_archive=$2

if [[ ! "$assets_sha" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Assets SHA must contain exactly 40 lowercase hexadecimal characters." >&2
    exit 64
fi

git archive --format=tar --output="$output_archive" HEAD public/assets
echo "Packaged public assets $assets_sha into $output_archive"
