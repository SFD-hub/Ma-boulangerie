#!/bin/bash
set -e

# APP_KEY is mandatory — fail fast with a clear message rather than a cryptic 500
if [ -z "$APP_KEY" ]; then
    echo "ERROR: APP_KEY is not set."
    echo "Generate one locally with:  php artisan key:generate --show"
    echo "Then add it to Render environment variables."
    exit 1
fi

echo "[entrypoint] Clearing config/route/view caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "[entrypoint] Running database migrations..."
php artisan migrate --force

echo "[entrypoint] Running database seeder..."
php artisan db:seed --force

echo "[entrypoint] Starting Apache..."
exec "$@"
