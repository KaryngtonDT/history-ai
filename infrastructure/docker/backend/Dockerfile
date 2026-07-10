# Symfony 7 backend — PHP 8.4, nginx + php-fpm, default AI pipeline CLIs.
# Build context: repository root (docker-compose build backend).

FROM php:8.4-fpm-bookworm

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        nginx \
        wget \
        curl \
        unzip \
        git \
        ffmpeg \
        python3 \
        python3-pip \
        python3-venv \
        python3-dev \
        build-essential \
        libsndfile1 \
        libgl1 \
        libglib2.0-0 \
        pkg-config \
        libavformat-dev \
        libavcodec-dev \
        libavdevice-dev \
        libavutil-dev \
        libswscale-dev \
        libswresample-dev \
        libavfilter-dev \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        $PHPIZE_DEPS \
    && pip3 install --break-system-packages faster-whisper yt-dlp huggingface_hub \
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

RUN composer dump-autoload --optimize --no-interaction

RUN composer run-script post-install-cmd --no-interaction \
    && mkdir -p var/cache var/log storage \
    && chown -R www-data:www-data var storage

COPY infrastructure/docker/backend/bin/faster-whisper \
     infrastructure/docker/backend/bin/f5-tts \
     infrastructure/docker/backend/bin/openvoice \
     infrastructure/docker/backend/bin/latentsync \
     infrastructure/docker/backend/bin/wav2lip \
     infrastructure/docker/backend/bin/whisper-cpp \
     infrastructure/docker/backend/bin/piper \
     infrastructure/docker/backend/bin/liveportrait \
     infrastructure/docker/backend/bin/musetalk \
     /usr/local/bin/
COPY infrastructure/docker/backend/placeholders/f5-tts \
     infrastructure/docker/backend/placeholders/openvoice \
     infrastructure/docker/backend/placeholders/latentsync \
     infrastructure/docker/backend/placeholders/wav2lip \
     infrastructure/docker/backend/placeholders/whisper-cpp \
     infrastructure/docker/backend/placeholders/piper \
     infrastructure/docker/backend/placeholders/liveportrait \
     infrastructure/docker/backend/placeholders/musetalk \
     /opt/lumen/placeholders/
COPY infrastructure/docker/backend/engines/ /opt/lumen/engines/
COPY scripts/install-f5-openvoice-latentsync.sh /opt/lumen/install-gpu-engines.sh
COPY scripts/install-latentsync.sh /opt/lumen/install-latentsync.sh
COPY scripts/install-wav2lip.sh /opt/lumen/install-wav2lip.sh
COPY scripts/install-whisper-cpp.sh /opt/lumen/install-whisper-cpp.sh
COPY scripts/install-piper.sh /opt/lumen/install-piper.sh
COPY scripts/install-liveportrait.sh /opt/lumen/install-liveportrait.sh
COPY scripts/install-musetalk.sh /opt/lumen/install-musetalk.sh
RUN chmod +x /usr/local/bin/faster-whisper \
             /usr/local/bin/f5-tts \
             /usr/local/bin/openvoice \
             /usr/local/bin/latentsync \
             /usr/local/bin/wav2lip \
             /usr/local/bin/whisper-cpp \
             /usr/local/bin/piper \
             /usr/local/bin/liveportrait \
             /usr/local/bin/musetalk \
             /opt/lumen/placeholders/f5-tts \
             /opt/lumen/placeholders/openvoice \
             /opt/lumen/placeholders/latentsync \
             /opt/lumen/placeholders/wav2lip \
             /opt/lumen/placeholders/whisper-cpp \
             /opt/lumen/placeholders/piper \
             /opt/lumen/placeholders/liveportrait \
             /opt/lumen/placeholders/musetalk \
             /opt/lumen/install-gpu-engines.sh \
             /opt/lumen/install-latentsync.sh \
             /opt/lumen/install-wav2lip.sh \
             /opt/lumen/install-whisper-cpp.sh \
             /opt/lumen/install-piper.sh \
             /opt/lumen/install-liveportrait.sh \
             /opt/lumen/install-musetalk.sh \
             /opt/lumen/engines/*.py \
 && sed -i 's/\r$//' /usr/local/bin/faster-whisper \
             /usr/local/bin/f5-tts \
             /usr/local/bin/openvoice \
             /usr/local/bin/latentsync \
             /usr/local/bin/wav2lip \
             /usr/local/bin/whisper-cpp \
             /usr/local/bin/piper \
             /usr/local/bin/liveportrait \
             /usr/local/bin/musetalk \
             /opt/lumen/placeholders/f5-tts \
             /opt/lumen/placeholders/openvoice \
             /opt/lumen/placeholders/latentsync \
             /opt/lumen/placeholders/wav2lip \
             /opt/lumen/placeholders/whisper-cpp \
             /opt/lumen/placeholders/piper \
             /opt/lumen/placeholders/liveportrait \
             /opt/lumen/placeholders/musetalk \
             /opt/lumen/install-gpu-engines.sh \
             /opt/lumen/install-latentsync.sh \
             /opt/lumen/install-wav2lip.sh \
             /opt/lumen/install-whisper-cpp.sh \
             /opt/lumen/install-piper.sh \
             /opt/lumen/install-liveportrait.sh \
             /opt/lumen/install-musetalk.sh \
             /opt/lumen/engines/*.py

COPY infrastructure/docker/backend/nginx.conf /etc/nginx/conf.d/default.conf
RUN rm -f /etc/nginx/sites-enabled/default
COPY infrastructure/docker/backend/entrypoint.sh /entrypoint.sh
RUN sed -i 's/\r$//' /entrypoint.sh && chmod +x /entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=5s --start-period=15s --retries=5 \
    CMD wget -q --spider http://127.0.0.1/health || exit 1

ENTRYPOINT ["/entrypoint.sh"]
