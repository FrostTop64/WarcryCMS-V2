#!/usr/bin/env bash
set -euo pipefail

# Best-effort: make user-writable areas writable by the current process.
# Container runs as www-data so chown is mostly a no-op; ignore failures.
for d in cache uploads admin/tempUploads resources/avatars; do
    if [ -d "/var/www/html/$d" ]; then
        chmod -R u+rwX,g+rwX "/var/www/html/$d" 2>/dev/null || true
    fi
done

exec "$@"
