#!/bin/sh

echo "PORT is: ${PORT}"

echo "Running migrations..."
php artisan migrate --force || echo "Migrate failed, continuing..."

echo "Starting server on port ${PORT:-8080}..."
exec php -S 0.0.0.0:${PORT:-8080} -t public 2>&1
