FROM composer:2 AS vendor
WORKDIR /app
COPY app/composer.json app/composer.lock ./
RUN composer install \
    --no-dev \
    --prefer-dist \
    --optimize-autoloader \
    --no-interaction \
    --no-scripts

FROM node:20-alpine AS frontend
WORKDIR /app
COPY app/package.json app/package-lock.json ./
RUN npm ci
COPY app/resources ./resources
COPY app/vite.config.js app/postcss.config.js app/tailwind.config.js ./
RUN npm run build

FROM php:8.3-cli-alpine
RUN apk add --no-cache bash libpq libzip icu-libs oniguruma \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS libpq-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-install pdo_pgsql bcmath mbstring intl zip \
    && apk del .build-deps

WORKDIR /var/www/html
COPY app ./
COPY --from=vendor /app/vendor ./vendor
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/views \
    storage/framework/cache \
    storage/framework/sessions \
    storage/logs \
    bootstrap/cache

RUN php artisan storage:link || true

EXPOSE 10000

CMD ["sh", "-c", "php artisan migrate --force && php artisan db:seed --class=DemoSeeder --force && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}"]
