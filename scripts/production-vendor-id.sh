#!/usr/bin/env bash
set -euo pipefail

repo_root=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
cd "$repo_root"

{
    cat composer.json composer.lock
    php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION, "\n";'
    uname -s
    uname -m
} | shasum | cut -d ' ' -f 1
