# PRT362S — Eben Supply | Group KN3
#
# Three stages: Composer packages, then the Vite build (it imports
# vendor/livewire/flux/dist/flux.css, so it needs vendor), then a slim
# PHP 8.4 + Apache image that serves public/.

# ── 1. PHP dependencies ─────────────────────────────────────────────────────
FROM composer:2.8 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist \
        --no-scripts --no-autoloader --ignore-platform-reqs

# ── 2. Front-end assets ─────────────────────────────────────────────────────
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
# Lock files made on Windows often miss Linux-only optional packages, which
# makes `npm ci` refuse; fall back to a normal install rather than fail
RUN npm ci --no-audit --no-fund || npm install --no-audit --no-fund
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY resources ./resources
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# ── 3. Runtime ──────────────────────────────────────────────────────────────
FROM php:8.4-apache AS app

RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        curl unzip libicu-dev libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip gd opcache \
 && a2enmod rewrite headers expires deflate \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-eben.ini
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --no-dev --optimize --no-scripts \
 && php artisan package:discover --ansi \
 && mkdir -p storage/app/public storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs bootstrap/cache \
 && chown -R www-data:www-data storage bootstrap/cache \
 && sed -i 's/\r$//' docker/entrypoint.sh \
 && chmod +x docker/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -fsS http://127.0.0.1:${PORT:-80}/up > /dev/null || exit 1

ENTRYPOINT ["docker/entrypoint.sh"]
CMD ["apache2-foreground"]
