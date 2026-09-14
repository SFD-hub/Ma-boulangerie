# ---- Stage 1 : build des assets front (Vite) ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY resources resources
COPY vite.config.js ./
COPY public public
RUN npm run build

# ---- Stage 2 : dépendances PHP (Composer) ----
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

# ---- Stage 3 : image finale (PHP-FPM + Nginx + Supervisor) ----
FROM php:8.3-fpm AS final

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor unzip curl default-mysql-client libzip-dev libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

COPY docker/nginx.conf /etc/nginx/sites-enabled/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-uploads.ini
COPY docker/www.conf /usr/local/etc/php-fpm.d/zz-www.conf

RUN rm -f /etc/nginx/sites-enabled/default.bak 2>/dev/null; \
    php artisan storage:link; \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
