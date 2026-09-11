#!/usr/bin/env bash
set -euo pipefail

repo_root=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$repo_root"

dependency_key=$( {
    cat package.json package-lock.json
    node --version
    uname -s
    uname -m
} | shasum | cut -d ' ' -f 1)
marker_file=node_modules/.kbelstisokoli-production-dependencies

if [ "${FORCE_NPM_CI:-0}" != 1 ] \
    && [ -d node_modules ] \
    && [ -f "$marker_file" ] \
    && [ "$(cat "$marker_file")" = "$dependency_key" ]; then
    echo "Reusing verified production Node dependencies $dependency_key."
    exit 0
fi

if [ -z "${FONTAWESOME_TOKEN:-}" ] && command -v security >/dev/null 2>&1; then
    FONTAWESOME_TOKEN=$(security find-generic-password -a "$(id -un)" -s 'kbelstisokoli.fontawesome-token' -w 2>/dev/null || true)
    export FONTAWESOME_TOKEN
fi

if [ -z "${FONTAWESOME_TOKEN:-}" ] && [ -f .env ]; then
    FONTAWESOME_TOKEN=$(php -r '
        foreach (file(".env", FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (str_starts_with(trim($line), "FONTAWESOME_TOKEN=")) {
                echo trim(trim(substr($line, strpos($line, "=") + 1)), "\"\\x27");
                break;
            }
        }
    ')
    export FONTAWESOME_TOKEN
fi

if [ -z "${FONTAWESOME_TOKEN:-}" ]; then
    echo "FONTAWESOME_TOKEN must be available in the environment, macOS Keychain, or local .env." >&2
    exit 1
fi

npm ci --no-audit --no-fund
printf '%s\n' "$dependency_key" > "$marker_file"
chmod 600 "$marker_file"
echo "Installed verified production Node dependencies $dependency_key."
