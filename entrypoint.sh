#!/bin/sh
set -e

echo "=== ENTRYPOINT START ==="

PORT="${PORT:-8000}"
RUN_MIGRATIONS="${RUN_MIGRATIONS:-true}"

if [ "$RUN_MIGRATIONS" = "true" ]; then
  php artisan migrate --force || echo "Migration failed, starting server anyway"
fi

exec php artisan serve --host=0.0.0.0 --port="$PORT"
