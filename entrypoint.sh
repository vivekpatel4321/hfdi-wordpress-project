#!/bin/bash
set -e

echo "[Entrypoint] Ensuring wp-content/uploads is writable..."

mkdir -p /var/www/html/public/wp-content/uploads
chown -R www-data:www-data /var/www/html/public/wp-content/uploads
chmod -R 777 /var/www/html/public/wp-content/uploads

exec php-fpm
