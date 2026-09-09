#!/usr/bin/env bash
set -euo pipefail

if [ "$#" -ne 6 ]; then
    echo "Usage: $0 RELEASE_SHA RELEASE_ARCHIVE VENDOR_SHA VENDOR_ARCHIVE ASSETS_SHA ASSETS_ARCHIVE" >&2
    exit 64
fi

: "${PRODUCTION_SSH_HOST:?PRODUCTION_SSH_HOST is required}"
: "${PRODUCTION_SSH_PORT:?PRODUCTION_SSH_PORT is required}"
: "${PRODUCTION_SSH_USER:?PRODUCTION_SSH_USER is required}"
: "${PRODUCTION_PATH:?PRODUCTION_PATH is required}"
: "${PRODUCTION_PUBLIC_PATH:?PRODUCTION_PUBLIC_PATH is required}"

release_sha=$1
release_source=$2
vendor_sha=$3
vendor_source=$4
assets_sha=$5
assets_source=$6
health_url=${HEALTH_URL:-https://kbelstisokoli.cz}
php_binary=${PHP_BINARY:-php8.4}

if [[ ! "$release_sha" =~ ^[0-9a-f]{40}$ ]] || [[ ! "$vendor_sha" =~ ^[0-9a-f]{40}$ ]] || [[ ! "$assets_sha" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Release, vendor and assets identifiers must be 40-character lowercase hashes." >&2
    exit 64
fi

for archive in "$release_source" "$vendor_source" "$assets_source"; do
    if [ ! -f "$archive" ]; then
        echo "Required archive is missing: $archive" >&2
        exit 1
    fi
done

sha256_file() {
    if command -v sha256sum >/dev/null 2>&1; then
        sha256sum "$1" | cut -d ' ' -f 1
    else
        shasum -a 256 "$1" | cut -d ' ' -f 1
    fi
}

retry_transport() {
    local attempt=1
    local max_attempts=4
    local status

    while true; do
        "$@" && return 0
        status=$?
        if [ "$status" -ne 255 ]; then
            return "$status"
        fi
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "SSH transport failed after $attempt attempts." >&2
            return "$status"
        fi
        echo "SSH transport attempt $attempt failed; retrying in 10 seconds." >&2
        attempt=$((attempt + 1))
        sleep 10
    done
}

remote="$PRODUCTION_SSH_USER@$PRODUCTION_SSH_HOST"
incoming="$PRODUCTION_PATH/deploy/incoming/$release_sha"
managed_assets="$PRODUCTION_PATH/deploy/managed-assets/$assets_sha"
managed_vendor="$PRODUCTION_PATH/deploy/managed-vendor/$vendor_sha"
release_archive="$incoming/release-$release_sha.tgz"
vendor_archive="$incoming/vendor-$vendor_sha.tgz"
assets_archive="$incoming/assets-$assets_sha.tar"
remote_script="$incoming/deploy-production-release-$release_sha.sh"
script_dir=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
release_checksum=$(sha256_file "$release_source")
vendor_checksum=$(sha256_file "$vendor_source")
assets_checksum=$(sha256_file "$assets_source")
ssh_options=(-4 -p "$PRODUCTION_SSH_PORT" -o BatchMode=yes -o StrictHostKeyChecking=yes -o ConnectTimeout=20 -o ConnectionAttempts=1 -o ServerAliveInterval=15 -o ServerAliveCountMax=4)
scp_options=(-4 -P "$PRODUCTION_SSH_PORT" -o BatchMode=yes -o StrictHostKeyChecking=yes -o ConnectTimeout=20 -o ConnectionAttempts=1 -o ServerAliveInterval=15 -o ServerAliveCountMax=4)

if [ -n "${PRODUCTION_SSH_KEY:-}" ]; then
    if [ ! -f "$PRODUCTION_SSH_KEY" ]; then
        echo "Configured production SSH key is missing." >&2
        exit 1
    fi
    ssh_options+=(-i "$PRODUCTION_SSH_KEY" -o IdentitiesOnly=yes)
    scp_options+=(-i "$PRODUCTION_SSH_KEY" -o IdentitiesOnly=yes)
fi

if [ -n "${PRODUCTION_SSH_KNOWN_HOSTS_FILE:-}" ]; then
    if [ ! -f "$PRODUCTION_SSH_KNOWN_HOSTS_FILE" ]; then
        echo "Configured production known_hosts file is missing." >&2
        exit 1
    fi
    ssh_options+=(-o "UserKnownHostsFile=$PRODUCTION_SSH_KNOWN_HOSTS_FILE")
    scp_options+=(-o "UserKnownHostsFile=$PRODUCTION_SSH_KNOWN_HOSTS_FILE")
fi

remote_bash() {
    local remote_command=$1

    retry_transport ssh "${ssh_options[@]}" "$remote" "bash -lc $(printf '%q' "$remote_command")"
}

# Promotion can switch the live release. Never retry it after an SSH disconnect:
# the server-side flock, checksum validation, and rollback handle one invocation.
remote_bash_once() {
    local remote_command=$1

    ssh "${ssh_options[@]}" "$remote" "bash -lc $(printf '%q' "$remote_command")"
}

remote_bash "mkdir -p $(printf '%q' "$incoming")"

remote_release_checksum=$(remote_bash \
    "if test -f $(printf '%q' "$release_archive"); then sha256sum $(printf '%q' "$release_archive") | cut -d ' ' -f 1; else printf missing; fi")
if [ "$remote_release_checksum" = "$release_checksum" ]; then
    echo "Release archive $release_sha is already present on production."
else
    retry_transport scp "${scp_options[@]}" "$release_source" "$remote:$release_archive"
fi

retry_transport scp "${scp_options[@]}" "$script_dir/deploy-production-release.sh" "$remote:$remote_script"

if remote_bash "test -d $(printf '%q' "$managed_vendor")"; then
    echo "Production vendor $vendor_sha is already present on production."
else
    remote_vendor_checksum=$(remote_bash \
        "if test -f $(printf '%q' "$vendor_archive"); then sha256sum $(printf '%q' "$vendor_archive") | cut -d ' ' -f 1; else printf missing; fi")
    if [ "$remote_vendor_checksum" = "$vendor_checksum" ]; then
        echo "Production vendor archive $vendor_sha is already present on production."
    else
        retry_transport scp "${scp_options[@]}" "$vendor_source" "$remote:$vendor_archive"
    fi
fi

if remote_bash "test -d $(printf '%q' "$managed_assets")"; then
    echo "Public assets $assets_sha are already present on production."
else
    remote_assets_checksum=$(remote_bash \
        "if test -f $(printf '%q' "$assets_archive"); then sha256sum $(printf '%q' "$assets_archive") | cut -d ' ' -f 1; else printf missing; fi")
    if [ "$remote_assets_checksum" = "$assets_checksum" ]; then
        echo "Public assets archive $assets_sha is already present on production."
    else
        retry_transport scp "${scp_options[@]}" "$assets_source" "$remote:$assets_archive"
    fi
fi

deploy_command="PRODUCTION_PATH=$(printf '%q' "$PRODUCTION_PATH") PRODUCTION_PUBLIC_PATH=$(printf '%q' "$PRODUCTION_PUBLIC_PATH") RELEASE_SHA=$(printf '%q' "$release_sha") RELEASE_ARCHIVE=$(printf '%q' "$release_archive") RELEASE_CHECKSUM=$(printf '%q' "$release_checksum") VENDOR_SHA=$(printf '%q' "$vendor_sha") VENDOR_ARCHIVE=$(printf '%q' "$vendor_archive") VENDOR_CHECKSUM=$(printf '%q' "$vendor_checksum") ASSETS_SHA=$(printf '%q' "$assets_sha") ASSETS_ARCHIVE=$(printf '%q' "$assets_archive") ASSETS_CHECKSUM=$(printf '%q' "$assets_checksum") HEALTH_URL=$(printf '%q' "$health_url") PHP_BINARY=$(printf '%q' "$php_binary") bash $(printf '%q' "$remote_script")"
remote_bash_once "$deploy_command"
