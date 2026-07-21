FROM php:8.2-apache AS php-base

ARG DEBIAN_FRONTEND=noninteractive

ENV APACHE_DOCUMENT_ROOT=/app/public \
    PORT=8080 \
    DB_CONNECTION=mysql \
    DB_HOST=placeholder \
    DB_DATABASE=placeholder \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    LOG_CHANNEL=stderr \
    LOG_LEVEL=info \
    DEBUGBAR_ENABLED=false

# Install PHP extensions, then remove build-only packages from the base image.
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev; \
    docker-php-ext-configure gd --with-freetype --with-jpeg; \
    docker-php-ext-install pdo_mysql mbstring xml bcmath gd zip opcache; \
    docker-php-source delete; \
    apt-get purge -y --auto-remove \
        $PHPIZE_DEPS \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libxml2-dev \
        libzip-dev; \
    apt-get install -y --no-install-recommends \
        libfreetype6 \
        libjpeg62-turbo \
        libonig5 \
        libpng16-16 \
        libxml2 \
        libzip5; \
    rm -rf /var/lib/apt/lists/*

# Enable Apache modules and configure Apache for Laravel/Railway at build time.
RUN set -eux; \
    a2enmod rewrite; \
    a2dismod mpm_event mpm_worker || true; \
    a2enmod mpm_prefork; \
    printf '%s\n' 'ServerName localhost' >> /etc/apache2/apache2.conf; \
    printf '%s\n' \
        '<IfModule mpm_prefork_module>' \
        '    StartServers          3' \
        '    MinSpareServers       3' \
        '    MaxSpareServers       5' \
        '    MaxRequestWorkers    20' \
        '    MaxConnectionsPerChild 1000' \
        '</IfModule>' > /etc/apache2/mods-available/mpm_prefork.conf; \
    sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf; \
    sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf; \
    printf '%s\n' \
        '<Directory /app/public>' \
        '    AllowOverride All' \
        '    Require all granted' \
        '</Directory>' > /etc/apache2/conf-available/laravel.conf; \
    a2enconf laravel; \
    sed -ri -e 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf; \
    sed -ri -e 's/:80/:${PORT}/g' /etc/apache2/sites-available/*.conf; \
    apachectl -t

# PHP performance tuning (OPcache + memory).
RUN set -eux; \
    printf '%s\n' \
        'opcache.enable=1' \
        'opcache.memory_consumption=128' \
        'opcache.interned_strings_buffer=16' \
        'opcache.max_accelerated_files=10000' \
        'opcache.validate_timestamps=0' \
        'opcache.save_comments=1' \
        'opcache.enable_cli=1' > /usr/local/etc/php/conf.d/opcache.ini; \
    printf '%s\n' \
        'memory_limit=256M' \
        'upload_max_filesize=64M' \
        'post_max_size=64M' \
        'max_execution_time=60' \
        'realpath_cache_size=4096K' \
        'realpath_cache_ttl=600' > /usr/local/etc/php/conf.d/performance.ini

WORKDIR /app

FROM php-base AS vendor

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends git unzip zip; \
    rm -rf /var/lib/apt/lists/*

COPY composer.json composer.lock ./
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction --prefer-dist

COPY . .
RUN composer dump-autoload --optimize --no-scripts

FROM node:22-bookworm-slim AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

FROM php-base AS runtime

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends gosu; \
    rm -rf /var/lib/apt/lists/*

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=vendor /app/bootstrap/cache ./bootstrap/cache
COPY --from=assets /app/public/build ./public/build

RUN set -eux; \
    mkdir -p \
        storage/framework/sessions \
        storage/framework/views \
        storage/framework/cache/data \
        storage/framework/testing \
        storage/app/public \
        storage/app/private \
        storage/logs \
        bootstrap/cache \
        /var/lock/apache2 \
        /var/log/apache2 \
        /var/run/apache2; \
    ln -sfn ../storage/app/public public/storage; \
    chmod -R u=rwX,g=rX,o=rX /app; \
    chown -R www-data:www-data storage bootstrap/cache /var/lock/apache2 /var/log/apache2 /var/run/apache2; \
    chmod -R u=rwX,g=rwX,o=rX storage bootstrap/cache

COPY start.sh /start.sh
RUN chmod 755 /start.sh

EXPOSE 8080

ENTRYPOINT ["/start.sh"]
CMD ["web"]
