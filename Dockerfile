FROM php:8.2-apache

# Enable mod_rewrite and suppress ServerName warning
RUN a2enmod rewrite && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl zip unzip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libzip-dev libonig-dev libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring xml bcmath gd zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# PHP performance tuning (OPcache + memory)
RUN echo 'opcache.enable=1\n\
opcache.memory_consumption=128\n\
opcache.interned_strings_buffer=16\n\
opcache.max_accelerated_files=10000\n\
opcache.validate_timestamps=0\n\
opcache.save_comments=1\n\
opcache.enable_cli=1' > /usr/local/etc/php/conf.d/opcache.ini \
    && echo 'memory_limit=256M\n\
upload_max_filesize=64M\n\
post_max_size=64M\n\
max_execution_time=60\n\
realpath_cache_size=4096K\n\
realpath_cache_ttl=600' > /usr/local/etc/php/conf.d/performance.ini

# Install Node.js 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configure Apache to serve from /app/public
ENV APACHE_DOCUMENT_ROOT=/app/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && echo '<Directory /app/public>\n    AllowOverride All\n    Require all granted\n</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# Set Railway port via Apache
RUN sed -ri -e 's/Listen 80/Listen ${PORT}/g' /etc/apache2/ports.conf \
    && sed -ri -e 's/:80/:${PORT}/g' /etc/apache2/sites-available/*.conf

WORKDIR /app

# Copy composer files first (Docker layer caching)
COPY composer.json composer.lock ./
ENV COMPOSER_ALLOW_SUPERUSER=1
# Prevent SQLite fallback during build (no DB available at build time)
ENV DB_CONNECTION=mysql
ENV DB_HOST=placeholder
ENV DB_DATABASE=placeholder
RUN composer install --optimize-autoloader --no-dev --no-scripts --no-interaction

# Copy package files and install
COPY package.json package-lock.json ./
RUN npm ci

# Copy entire project
COPY . .

# Build frontend assets
RUN npm run build

# Optimize autoloader and discover packages at build time
RUN composer dump-autoload --optimize --no-scripts 2>/dev/null || true

# Setup Laravel storage directories
RUN mkdir -p storage/framework/{sessions,views,cache,testing} storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache \
    && chown -R www-data:www-data /app

# Copy startup script
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE ${PORT:-8080}

CMD ["/start.sh"]
