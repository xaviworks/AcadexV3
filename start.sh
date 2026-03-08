#!/bin/bash
set -e

# Fix Apache MPM (must happen before any Apache command)
rm -f /etc/apache2/mods-enabled/mpm_*
ln -sf /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load
ln -sf /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf

echo ">>> Running migrations..."
php artisan migrate --force

# Only seed if the database is empty (first deploy)
# Use a direct PHP script — avoids booting PsySH/tinker (~2-3s startup cost)
USER_COUNT=$(php -r "
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo \App\Models\User::count();
" 2>/dev/null || echo "0")
if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
    echo ">>> First deploy detected — running seeders..."
    php artisan db:seed --force
else
    echo ">>> Database already seeded ($USER_COUNT users) — skipping seeders."
fi

php artisan storage:link 2>/dev/null || true

echo ">>> Discovering packages..."
php artisan package:discover --ansi

echo ">>> Caching config, routes, views, events..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ">>> Starting Apache on port ${PORT:-8080}..."
exec apache2-foreground
