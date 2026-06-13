FROM php:8.3-apache

# System dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libicu-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
# pdo_mysql intentionally omitted: the app uses PostgreSQL on Render and the
# config/database.php file references Pdo\Mysql (PHP 8.4+). Omitting pdo_mysql
# short-circuits the extension_loaded() guard and avoids a fatal error on PHP 8.3.
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    mbstring \
    tokenizer \
    xml \
    ctype \
    bcmath \
    fileinfo \
    intl \
    zip \
    opcache

# OPcache tuning
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.max_accelerated_files=10000"; \
    echo "opcache.revalidate_freq=0"; \
    echo "opcache.validate_timestamps=0"; \
} > /usr/local/etc/php/conf.d/opcache.ini

# Apache modules
RUN a2enmod rewrite headers

# Apache virtual host
COPY docker/000-default.conf /etc/apache2/sites-available/000-default.conf

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies (separate layer for cache efficiency)
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --no-scripts \
    --no-autoloader

# Copy application code (vendor excluded via .dockerignore)
COPY . .

# Generate optimised autoloader with actual app classes in place
RUN composer dump-autoload --optimize

# Storage and cache directories must be writable by the web server
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]
