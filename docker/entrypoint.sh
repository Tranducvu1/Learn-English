#!/bin/bash
set -e

# Render injects RENDER_EXTERNAL_URL (https://hanviet.onrender.com)
if [ -z "$APP_URL" ] && [ -n "$RENDER_EXTERNAL_URL" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    echo "Generate: php artisan key:generate --show"
    exit 1
fi

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ -n "$DATABASE_URL" ] && [ -z "$DB_URL" ]; then
    export DB_URL="$DATABASE_URL"
fi

if [ -n "$DATABASE_URL" ] || [ -n "$DB_URL" ]; then
    export DB_CONNECTION="${DB_CONNECTION:-pgsql}"
fi

php artisan storage:link --force 2>/dev/null || true
php artisan migrate --force
php artisan db:seed --force 2>/dev/null || true
php artisan app:enrich-vietnamese 2>/dev/null || true
php artisan app:publish-frontend --force 2>/dev/null || true

exec php artisan serve --host=0.0.0.0 --port="${PORT:-10000}"
