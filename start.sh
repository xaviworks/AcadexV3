#!/bin/bash
set -Eeuo pipefail

log() {
    printf '[container] %s\n' "$*"
}

warn() {
    printf '[container][warn] %s\n' "$*" >&2
}

fatal() {
    local status="$1"
    shift
    printf '[container][fatal] %s (exit code: %s)\n' "$*" "$status" >&2
    exit "$status"
}

on_error() {
    local status="$?"
    printf '[container][error] command failed (exit code: %s): %s\n' "$status" "$BASH_COMMAND" >&2
    printf '[container][error] working directory: %s\n' "$PWD" >&2
    exit "$status"
}

trap on_error ERR

run_as_app() {
    if [ "$(id -u)" = "0" ]; then
        gosu www-data "$@"
    else
        "$@"
    fi
}

exec_as_app() {
    if [ "$(id -u)" = "0" ]; then
        exec gosu www-data "$@"
    fi

    exec "$@"
}

ROLE="${1:-web}"

log "Container startup beginning."
log "Container role: ${ROLE}"
log "Runtime user: $(id -un) (uid=$(id -u), gid=$(id -g)); port: ${PORT:-8080}"
log "Application environment: ${APP_ENV:-not-set}; debug: ${APP_DEBUG:-not-set}"
if [ "${APP_DEBUG:-false}" = "true" ] || [ "${APP_DEBUG:-0}" = "1" ]; then
    log "Database target: ${DB_CONNECTION:-not-set}@${DB_HOST:-not-set}:${DB_PORT:-3306}/${DB_DATABASE:-not-set}"
fi
log "Cache/session/queue: ${CACHE_STORE:-not-set}/${SESSION_DRIVER:-not-set}/${QUEUE_CONNECTION:-not-set}"

log "PHP version: $(php -r 'echo PHP_VERSION;')"
log "Loaded PHP extensions: $(php -m | tr '\n' ' ' | sed 's/[[:space:]]\+/ /g')"

if [ "$ROLE" = "web" ]; then
    a2dismod mpm_event >/dev/null 2>&1 || true
    a2dismod mpm_worker >/dev/null 2>&1 || true
    a2enmod mpm_prefork >/dev/null 2>&1 || true
    log "Apache configuration check..."
    apachectl -t
fi

if ! run_as_app test -r /app/artisan; then
    fatal 1 "Laravel artisan file is missing or unreadable"
fi

log "Laravel version: $(run_as_app php artisan --version)"

log "Creating required storage directories..."
mkdir -p storage/framework/views \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/app/public \
         storage/app/private \
         storage/logs

if [ "$(id -u)" = "0" ]; then
    mkdir -p /var/lock/apache2 /var/log/apache2 /var/run/apache2
    chown -R www-data:www-data storage bootstrap/cache /var/lock/apache2 /var/log/apache2 /var/run/apache2
fi

chmod -R u=rwX,g=rwX,o=rX storage bootstrap/cache

if ! run_as_app test -w storage || ! run_as_app test -w bootstrap/cache; then
    fatal 1 "Laravel writable directories remain unavailable after permission setup"
fi

if [ "${RUN_MIGRATIONS:-}" = "true" ] || { [ -z "${RUN_MIGRATIONS:-}" ] && [ "$ROLE" = "web" ]; }; then
    log "Running database migrations..."
    run_as_app php artisan migrate --force
    log "Database migrations completed."

    # Only seed if the database is empty (first deploy).
    # Use a direct PHP script to avoid booting PsySH/tinker during startup.
    if ! USER_COUNT=$(run_as_app php -r "
require '/app/vendor/autoload.php';
\$app = require '/app/bootstrap/app.php';
\$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo \App\Models\User::count();
" ); then
        fatal 1 "Unable to query the users table; refusing to guess whether seeders should run"
    fi

    if [ "$USER_COUNT" = "0" ] || [ -z "$USER_COUNT" ]; then
        log "No users found; running seeders for the first deployment..."
        run_as_app php artisan db:seed --force
        log "Database seeders completed."
    else
        log "Database already seeded ($USER_COUNT users); skipping seeders."
    fi
else
    log "Skipping migrations for role '${ROLE}'."
fi

log "Ensuring public storage link exists..."
if ! run_as_app php artisan storage:link; then
    warn "Storage link could not be created; continuing because it may already exist."
fi

log "Discovering Laravel packages..."
run_as_app php artisan package:discover --ansi

log "Caching configuration, routes, views, and events..."
run_as_app php artisan config:cache
run_as_app php artisan route:cache
run_as_app php artisan view:cache
run_as_app php artisan event:cache

case "$ROLE" in
    web)
        log "Starting Apache on port ${PORT:-8080} as www-data..."
        exec_as_app apache2-foreground
        ;;
    queue)
        log "Starting Laravel queue worker in foreground."
        exec_as_app php artisan queue:work --sleep="${QUEUE_SLEEP:-3}" --tries="${QUEUE_TRIES:-3}" --timeout="${QUEUE_TIMEOUT:-90}" --max-time="${QUEUE_MAX_TIME:-3600}"
        ;;
    scheduler)
        log "Starting Laravel scheduler in foreground."
        exec_as_app php artisan schedule:work
        ;;
    *)
        log "Running custom command: $*"
        exec_as_app "$@"
        ;;
esac
