#!/bin/bash
set -e

echo ">>> Optimizing autoloader..."
composer dump-autoload --optimize --no-scripts

echo ">>> Discovering packages..."
php artisan package:discover --ansi

echo ">>> Running migrations..."
php artisan migrate --force

echo ">>> Running seeders..."
php artisan db:seed --force

echo ">>> Creating storage link..."
php artisan storage:link 2>/dev/null || true

echo ">>> Caching config, routes, views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ">>> Starting Apache on port ${PORT:-8080}..."
exec apache2-foreground
