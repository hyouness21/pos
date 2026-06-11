#!/bin/sh

echo "Running migrations..."
php artisan migrate --force || echo "Migrate failed, continuing..."

echo "PORT is: ${PORT}"
echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080} 2>&1
