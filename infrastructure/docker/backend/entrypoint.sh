#!/bin/sh
set -e

mkdir -p \
  /var/www/html/storage/uploads/video /var/www/html/storage/uploads/audio /var/www/html/storage/uploads/pdf \
  /var/www/html/storage/artifacts/transcript /var/www/html/storage/artifacts/translation /var/www/html/storage/artifacts/audio \
  /var/www/html/storage/artifacts/voiceclone /var/www/html/storage/artifacts/lipsync /var/www/html/storage/artifacts/render /var/www/html/storage/artifacts/quality \
  /var/www/html/storage/shadow/identity /var/www/html/storage/shadow/sessions \
  /var/www/html/storage/shadow/session-learning \
  /var/www/html/storage/shadow/relationship \
  /var/www/html/storage/shadow/memory \
  /var/www/html/storage/learning /var/www/html/storage/workspace /var/www/html/storage/logs /var/www/html/storage/temp /var/www/html/storage/cache

chown -R www-data:www-data /var/www/html/storage

php-fpm -D
exec nginx -g 'daemon off;'
