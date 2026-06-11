#!/bin/sh
set -e

echo "Running migrations..."
php artisan migrate --force

echo "Linking storage..."
php artisan storage:link 2>/dev/null || true

echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
