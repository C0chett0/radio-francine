# syntax=docker/dockerfile:1.7

ARG PHP_VERSION=8.4
ARG NODE_VERSION=22
ARG WWWUSER=1000
ARG WWWGROUP=1000

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --prefer-dist --no-progress --no-scripts

FROM php:${PHP_VERSION}-fpm-alpine AS base
ARG WWWUSER
ARG WWWGROUP
ARG NODE_VERSION
RUN set -eux; \
    apk add --no-cache bash git curl icu-dev oniguruma-dev libpq-dev libzip-dev nodejs npm; \
    docker-php-ext-install intl pdo_pgsql bcmath opcache zip; \
    addgroup -g "${WWWGROUP}" app || true; \
    adduser -D -G app -u "${WWWUSER}" app || true; \
    apk del --no-cache git
WORKDIR /var/www/html
ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_KEY=base64:0000000000000000000000000000000000000000000= \
    LOG_CHANNEL=stderr
RUN set -eux; \
    mkdir -p storage/framework/cache storage/framework/views storage/framework/sessions storage/logs bootstrap/cache; \
    chown -R app:app storage bootstrap/cache

FROM base AS assets
COPY package*.json ./
RUN npm ci --no-progress
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

FROM base AS app
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /var/www/html/public/build ./public/build

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

RUN chown -R app:app /var/www/html
USER app

CMD ["php-fpm"]

FROM caddy:2-alpine AS web
WORKDIR /srv
COPY docker/caddy/Caddyfile.prod /etc/caddy/Caddyfile
COPY --from=assets /var/www/html/public /var/www/html/public

