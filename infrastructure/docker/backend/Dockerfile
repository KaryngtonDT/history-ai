# Symfony 7 backend — PHP 8.4, nginx + php-fpm, default AI pipeline CLIs.
# Build context: repository root (docker-compose build backend).

FROM php:8.4-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        wget \
        curl \
        unzip \
        ffmpeg \
        python3 \
        python3-pip \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        $PHPIZE_DEPS \
    && pip3 install --break-system-packages faster-whisper yt-dlp \
    && docker-php-ext-install -j"$(nproc)" intl opcache pdo_pgsql zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/* /root/.cache/pip

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
    && mkdir -p var/cache var/log storage \
    && chown -R www-data:www-data var storage

COPY infrastructure/docker/backend/bin/faster-whisper \
     infrastructure/docker/backend/bin/f5-tts \
     infrastructure/docker/backend/bin/openvoice \
     infrastructure/docker/backend/bin/latentsync \
     /usr/local/bin/
RUN chmod +x /usr/local/bin/faster-whisper \
             /usr/local/bin/f5-tts \
             /usr/local/bin/openvoice \
             /usr/local/bin/latentsync

COPY infrastructure/docker/backend/nginx.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default
COPY infrastructure/docker/backend/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=5s --start-period=15s --retries=5 \
    CMD wget -q --spider http://127.0.0.1/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]
