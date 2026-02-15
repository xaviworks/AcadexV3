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

echo ">>> Fixing Apache MPM (ensure only prefork is loaded)..."
ls -la /etc/apache2/mods-enabled/mpm_* 2>/dev/null || true
rm -f /etc/apache2/mods-enabled/mpm_*
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf
echo ">>> MPM modules after fix:"
ls -la /etc/apache2/mods-enabled/mpm_* 2>/dev/null || true
apache2ctl configtest 2>&1 || true

echo ">>> Starting Apache on port ${PORT:-8080}..."
exec apache2-foreground
