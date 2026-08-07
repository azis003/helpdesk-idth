# syntax=docker/dockerfile:1.7

FROM node:22-alpine AS frontend

WORKDIR /var/www/html
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY . .
RUN npm run build

FROM composer:2.8 AS vendor

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

FROM php:8.3-fpm-alpine AS app

WORKDIR /var/www/html

RUN apk add --no-cache icu-libs libpq libxml2 oniguruma \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libpq-dev libxml2-dev oniguruma-dev \
    && docker-php-ext-install pdo_pgsql mbstring intl xml pcntl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis opcache \
    && apk del .build-deps

COPY deploy/php.ini /usr/local/etc/php/conf.d/99-sihati.ini
COPY --from=vendor /var/www/html/vendor ./vendor
COPY . .
COPY --from=frontend /var/www/html/public/build ./public/build

RUN mkdir -p \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

FROM nginx:1.27-alpine AS web

COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf
COPY --from=app /var/www/html/public /var/www/html/public
