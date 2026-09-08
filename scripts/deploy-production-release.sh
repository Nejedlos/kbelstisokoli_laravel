#!/usr/bin/env bash
set -euo pipefail

: "${PRODUCTION_PATH:?PRODUCTION_PATH is required}"
: "${PRODUCTION_PUBLIC_PATH:?PRODUCTION_PUBLIC_PATH is required}"
: "${RELEASE_SHA:?RELEASE_SHA is required}"
: "${RELEASE_ARCHIVE:?RELEASE_ARCHIVE is required}"
: "${RELEASE_CHECKSUM:?RELEASE_CHECKSUM is required}"
: "${VENDOR_SHA:?VENDOR_SHA is required}"
: "${VENDOR_ARCHIVE:?VENDOR_ARCHIVE is required}"
: "${VENDOR_CHECKSUM:?VENDOR_CHECKSUM is required}"
: "${ASSETS_SHA:?ASSETS_SHA is required}"
: "${ASSETS_ARCHIVE:?ASSETS_ARCHIVE is required}"
: "${ASSETS_CHECKSUM:?ASSETS_CHECKSUM is required}"
: "${HEALTH_URL:?HEALTH_URL is required}"

php_binary=${PHP_BINARY:-php8.4}
curl_binary=${CURL_BINARY:-curl}
deploy_root="$PRODUCTION_PATH/deploy"
releases_root="$deploy_root/releases"
release_path="$releases_root/$RELEASE_SHA"
current_link="$deploy_root/current"
previous_link="$deploy_root/previous"
temporary_release="$releases_root/.prepare-$RELEASE_SHA-$$"
bootstrap_backup=''
bootstrap_started=false
bootstrap_index_changed=false
bootstrap_changed_entries=()
release_created=false
current_switched=false
old_target=''
headers_file=''
maintenance_started=false
partner_source_path=''
shared_root="$deploy_root/shared"
shared_partners="$shared_root/assets-partners"
managed_assets_root="$deploy_root/managed-assets"
managed_assets_path="$managed_assets_root/$ASSETS_SHA"
managed_assets_prepare="$managed_assets_root/.prepare-$ASSETS_SHA-$$"
managed_vendor_root="$deploy_root/managed-vendor"
managed_vendor_path="$managed_vendor_root/$VENDOR_SHA"
managed_vendor_prepare="$managed_vendor_root/.prepare-$VENDOR_SHA-$$"
public_release_entries=(
    assets build js css fonts images
    android-chrome-192x192.png android-chrome-512x512.png apple-touch-icon.png
    favicon-16x16.png favicon-32x32.png favicon.ico
    llms.txt manifest.json robots.txt site.webmanifest sitemap.xml
)

if [[ ! "$RELEASE_SHA" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Invalid release SHA." >&2
    exit 64
fi

if [[ ! "$ASSETS_SHA" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Invalid assets SHA." >&2
    exit 64
fi
if [[ ! "$VENDOR_SHA" =~ ^[0-9a-f]{40}$ ]]; then
    echo "Invalid vendor SHA." >&2
    exit 64
fi

for required_path in "$PRODUCTION_PATH/.env" "$PRODUCTION_PATH/storage" "$PRODUCTION_PATH/resources/icons" "$PRODUCTION_PUBLIC_PATH/uploads" "$PRODUCTION_PUBLIC_PATH/storage" "$PRODUCTION_PUBLIC_PATH/assets"; do
    if [ ! -e "$required_path" ]; then
        echo "Required persistent path is missing: $required_path" >&2
        exit 1
    fi
done

mkdir -p "$releases_root" "$deploy_root/bootstrap-backups" "$managed_assets_root" "$managed_vendor_root"
exec 9>"$deploy_root/deployment.lock"
flock -w 300 9

rollback_bootstrap() {
    if [ "$bootstrap_started" != true ] || [ -z "$bootstrap_backup" ]; then
        return
    fi

    set +e
    if [ "$bootstrap_index_changed" = true ] && [ -f "$bootstrap_backup/index.php" ]; then
        install -m 0644 "$bootstrap_backup/index.php" "$PRODUCTION_PUBLIC_PATH/index.php.rollback"
        mv -f "$PRODUCTION_PUBLIC_PATH/index.php.rollback" "$PRODUCTION_PUBLIC_PATH/index.php"
    fi

    for entry in "${bootstrap_changed_entries[@]}"; do
        if [ -L "$PRODUCTION_PUBLIC_PATH/$entry" ]; then
            rm -f "$PRODUCTION_PUBLIC_PATH/$entry"
        fi
        if [ -e "$bootstrap_backup/$entry" ]; then
            mv "$bootstrap_backup/$entry" "$PRODUCTION_PUBLIC_PATH/$entry"
        fi
    done

    set -e
}

rollback_current() {
    if [ "$current_switched" != true ]; then
        return
    fi

    if [ -n "$old_target" ]; then
        ln -s "$old_target" "$deploy_root/.rollback-$RELEASE_SHA"
        mv -Tf "$deploy_root/.rollback-$RELEASE_SHA" "$current_link"
    else
        rm -f "$current_link"
    fi
}

resume_after_partner_move() {
    if [ -n "$partner_source_path" ] && [ -d "$shared_partners" ] && [ ! -e "$partner_source_path" ]; then
        ln -s "$shared_partners" "$partner_source_path"
    fi

    if [ "$maintenance_started" = true ]; then
        "$php_binary" "$active_artisan" up --no-interaction
        maintenance_started=false
    fi
}

cleanup() {
    status=$?
    set +e
    resume_after_partner_move
    if [ "$status" -ne 0 ]; then
        if [ -z "$old_target" ]; then
            rollback_bootstrap
            rollback_current
        else
            rollback_current
            rollback_bootstrap
        fi
        if [ "$release_created" = true ] && [ "$current_switched" != true ]; then
            rm -rf "$release_path"
        fi
    fi
    rm -rf "$temporary_release"
    rm -rf "$managed_assets_prepare"
    rm -rf "$managed_vendor_prepare"
    if [ -n "$headers_file" ]; then
        rm -f "$headers_file"
    fi
    exit "$status"
}
trap cleanup EXIT

printf '%s  %s\n' "$RELEASE_CHECKSUM" "$RELEASE_ARCHIVE" | sha256sum -c -

if [ ! -d "$managed_vendor_path" ]; then
    printf '%s  %s\n' "$VENDOR_CHECKSUM" "$VENDOR_ARCHIVE" | sha256sum -c -
    mkdir "$managed_vendor_prepare"
    tar -xzf "$VENDOR_ARCHIVE" -C "$managed_vendor_prepare"
    extracted_vendor="$managed_vendor_prepare/vendor"
    if [ ! -f "$extracted_vendor/autoload.php" ] || [ -L "$extracted_vendor" ]; then
        echo 'Vendor archive is incomplete or contains an unexpected symlink.' >&2
        exit 1
    fi
    printf '%s\n' "$VENDOR_SHA" > "$extracted_vendor/.vendor-sha"
    mv "$extracted_vendor" "$managed_vendor_path"
fi
if [ "$(cat "$managed_vendor_path/.vendor-sha" 2>/dev/null || true)" != "$VENDOR_SHA" ]; then
    echo 'Managed production vendor has an unexpected marker.' >&2
    exit 1
fi

if [ -L "$current_link" ]; then
    old_target=$(readlink "$current_link")
fi

if [ ! -d "$managed_assets_path" ]; then
    printf '%s  %s\n' "$ASSETS_CHECKSUM" "$ASSETS_ARCHIVE" | sha256sum -c -
    mkdir "$managed_assets_prepare"
    tar -xf "$ASSETS_ARCHIVE" -C "$managed_assets_prepare"
    extracted_assets="$managed_assets_prepare/public/assets"

    if [ ! -d "$extracted_assets" ] || [ -L "$extracted_assets/img/partners" ]; then
        echo 'Assets archive is incomplete or contains an unexpected partners symlink.' >&2
        exit 1
    fi

    source_public_assets="$PRODUCTION_PUBLIC_PATH/assets"
    if [ -L "$current_link" ] && [ -d "$current_link/public/assets" ]; then
        source_public_assets="$current_link/public/assets"
    fi

    mkdir -p "$shared_root"
    if [ ! -d "$shared_partners" ]; then
        partner_source_path="$source_public_assets/img/partners"
        if [ -L "$partner_source_path" ]; then
            echo 'Existing partners path is an unexpected symlink.' >&2
            exit 1
        fi

        active_artisan="$PRODUCTION_PATH/artisan"
        if [ -L "$current_link" ] && [ -f "$current_link/artisan" ]; then
            active_artisan="$current_link/artisan"
        fi
        if [ ! -f "$PRODUCTION_PATH/storage/framework/maintenance.php" ]; then
            "$php_binary" "$active_artisan" down --retry=5 --no-interaction
            maintenance_started=true
        fi

        # Maintenance stops new requests. Wait for every already-running PHP
        # request owned by this hosting account before moving the physical path.
        for _ in $(seq 1 300); do
            if ! ps -u "$(id -u)" -o comm= | grep -Eq '(^|/)(lsphp|php-cgi|php-fpm|php8?([.]?[0-9]+)?)$'; then
                break
            fi
            sleep 1
        done
        if ps -u "$(id -u)" -o comm= | grep -Eq '(^|/)(lsphp|php-cgi|php-fpm|php8?([.]?[0-9]+)?)$'; then
            echo 'Timed out while waiting for active PHP requests to finish.' >&2
            exit 1
        fi

        # Moving the original directory preserves every file and makes rollback
        # use the same writable path.
        if [ -d "$partner_source_path" ]; then
            mv "$partner_source_path" "$shared_partners"
        else
            mkdir "$shared_partners"
        fi
        ln -s "$shared_partners" "$partner_source_path"
        resume_after_partner_move
    fi

    rsync -a --ignore-existing --exclude='/img/partners/***' "$source_public_assets/" "$extracted_assets/"
    if [ -d "$extracted_assets/img/partners" ]; then
        cp -a "$extracted_assets/img/partners/." "$shared_partners/"
        rm -rf "$extracted_assets/img/partners"
    fi
    mkdir -p "$extracted_assets/img"
    ln -s "$shared_partners" "$extracted_assets/img/partners"
    printf '%s\n' "$ASSETS_SHA" > "$extracted_assets/.assets-sha"
    mv "$extracted_assets" "$managed_assets_path"
fi

if [ "$(cat "$managed_assets_path/.assets-sha" 2>/dev/null || true)" != "$ASSETS_SHA" ]; then
    echo 'Managed public assets have an unexpected marker.' >&2
    exit 1
fi

if [ ! -d "$release_path" ]; then
    mkdir "$temporary_release"
    tar -xzf "$RELEASE_ARCHIVE" -C "$temporary_release"

    if [ "$(cat "$temporary_release/.release-sha" 2>/dev/null || true)" != "$RELEASE_SHA" ]; then
        echo "Archive release marker does not match RELEASE_SHA." >&2
        exit 1
    fi

    if [ -e "$temporary_release/.env" ] || [ -e "$temporary_release/vendor" ] || [ ! -f "$temporary_release/public/build/manifest.json" ]; then
        echo "Release archive is incomplete or contains a forbidden environment file." >&2
        exit 1
    fi

    ln -s "$PRODUCTION_PATH/.env" "$temporary_release/.env"
    ln -s "$managed_vendor_path" "$temporary_release/vendor"
    ln -s "$PRODUCTION_PATH/storage" "$temporary_release/storage"
    mkdir -p "$temporary_release/resources"
    ln -s "$PRODUCTION_PATH/resources/icons" "$temporary_release/resources/icons"
    ln -s "$PRODUCTION_PUBLIC_PATH/uploads" "$temporary_release/public/uploads"
    ln -s "$PRODUCTION_PUBLIC_PATH/storage" "$temporary_release/public/storage"
    ln -s "$managed_assets_path" "$temporary_release/public/assets"
    mkdir -p "$temporary_release/bootstrap/cache" "$temporary_release/public/build/assets"
    chmod -R ug+rwX "$temporary_release/bootstrap/cache"

    if [ -L "$current_link" ] && [ -d "$current_link/public/build/assets" ]; then
        cp -a -n "$current_link/public/build/assets/." "$temporary_release/public/build/assets/"
    elif [ -d "$PRODUCTION_PUBLIC_PATH/build/assets" ] && [ ! -L "$PRODUCTION_PUBLIC_PATH/build" ]; then
        cp -a -n "$PRODUCTION_PUBLIC_PATH/build/assets/." "$temporary_release/public/build/assets/"
    fi

    mv "$temporary_release" "$release_path"
    release_created=true

    "$php_binary" "$release_path/artisan" migrate --force --no-interaction
    "$php_binary" "$release_path/artisan" filament:optimize
    "$php_binary" "$release_path/artisan" optimize --no-interaction
fi

if [ "$(cat "$release_path/.release-sha" 2>/dev/null || true)" != "$RELEASE_SHA" ]; then
    echo "Existing release directory has an unexpected marker." >&2
    exit 1
fi

for entry in "${public_release_entries[@]}"; do
    if [ ! -e "$release_path/public/$entry" ]; then
        echo "Release is missing public entry: $entry" >&2
        exit 1
    fi
done

if [ -z "$old_target" ]; then
    bootstrap_backup="$deploy_root/bootstrap-backups/$(date -u +%Y%m%dT%H%M%SZ)-$RELEASE_SHA"
    mkdir "$bootstrap_backup"
    cp -p "$PRODUCTION_PUBLIC_PATH/index.php" "$bootstrap_backup/index.php"

    for entry in "${public_release_entries[@]}"; do
        if [ -L "$PRODUCTION_PUBLIC_PATH/$entry" ]; then
            echo "Unexpected public symlink during first release bootstrap: $entry" >&2
            exit 1
        fi
    done

    bootstrap_started=true
fi

if [ "$old_target" != "releases/$RELEASE_SHA" ]; then
    ln -s "releases/$RELEASE_SHA" "$deploy_root/.current-$RELEASE_SHA"
    current_switched=true
    mv -Tf "$deploy_root/.current-$RELEASE_SHA" "$current_link"
fi

if [ -z "$old_target" ]; then
    for entry in "${public_release_entries[@]}"; do
        bootstrap_changed_entries+=("$entry")
        if [ -e "$PRODUCTION_PUBLIC_PATH/$entry" ]; then
            mv "$PRODUCTION_PUBLIC_PATH/$entry" "$bootstrap_backup/$entry"
        fi
        ln -s "../secret/deploy/current/public/$entry" "$PRODUCTION_PUBLIC_PATH/.$entry-$RELEASE_SHA"
        mv -Tf "$PRODUCTION_PUBLIC_PATH/.$entry-$RELEASE_SHA" "$PRODUCTION_PUBLIC_PATH/$entry"
    done

    install -m 0644 "$release_path/public/index.production.php" "$PRODUCTION_PUBLIC_PATH/index.php.new"
    bootstrap_index_changed=true
    mv -f "$PRODUCTION_PUBLIC_PATH/index.php.new" "$PRODUCTION_PUBLIC_PATH/index.php"
fi

headers_file="$deploy_root/.health-$RELEASE_SHA-$$"
health_base=${HEALTH_URL%/}
for health_path in /up / /treninky /akce /zapasy /tymy; do
    if ! "$curl_binary" -fsS --max-time 30 -D "$headers_file" -o /dev/null "$health_base$health_path?release=$RELEASE_SHA"; then
        echo "Production health request failed for $health_path; current will be restored." >&2
        exit 1
    fi
    if ! tr -d '\r' < "$headers_file" | grep -Fxi "X-App-Release: $RELEASE_SHA" >/dev/null; then
        echo "Production reported an unexpected release for $health_path; current will be restored." >&2
        exit 1
    fi
done
rm -f "$headers_file"
headers_file=''

if [ -n "$old_target" ] && [ "$old_target" != "releases/$RELEASE_SHA" ]; then
    ln -s "$old_target" "$deploy_root/.previous-$RELEASE_SHA"
    mv -Tf "$deploy_root/.previous-$RELEASE_SHA" "$previous_link"
fi

bootstrap_started=false
rm -f "$RELEASE_ARCHIVE"
rm -f "$VENDOR_ARCHIVE"
rm -f "$ASSETS_ARCHIVE"
echo "Production release $RELEASE_SHA is active."
