#!/usr/bin/env bash
set -euo pipefail

# Make user-writable areas writable by www-data even when bind-mounted from a
# Windows host (where chown is a no-op but chmod still helps).
for d in cache uploads admin/tempUploads resources/avatars; do
    if [ -d "/var/www/html/$d" ]; then
        chmod -R u+rwX,g+rwX "/var/www/html/$d" 2>/dev/null || true
        chown -R www-data:www-data "/var/www/html/$d" 2>/dev/null || true
    fi
done

exec "$@"
