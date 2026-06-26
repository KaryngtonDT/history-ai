# Symfony 7 backend — PHP 8.4, nginx + php-fpm.
# Build context: repository root (docker-compose build backend).

FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        nginx \
        wget \
        curl \
        icu-dev \
        postgresql-dev \
        linux-headers \
        $PHPIZE_DEPS \
    && docker-php-ext-install -j"$(nproc)" intl opcache pdo_pgsql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del --no-cache $PHPIZE_DEPS \
    && rm -rf /var/cache/apk/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY backend/composer.json backend/composer.lock backend/symfony.lock ./
COPY backend/bin bin/
COPY backend/config config/
COPY backend/public public/
COPY backend/src src/

RUN composer install --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY backend/ .

RUN composer run-script post-install-cmd --no-interaction \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var

COPY infrastructure/docker/backend/nginx.conf /etc/nginx/http.d/default.conf
COPY infrastructure/docker/backend/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=5s --start-period=15s --retries=5 \
    CMD wget -q --spider http://127.0.0.1/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]
