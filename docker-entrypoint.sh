#!/bin/sh
set -e

# Render provides PORT; nginx listens on it.
PORT="${PORT:-80}"
sed -i "s/listen [0-9]*;/listen $PORT;/g" /etc/nginx/sites-available/default

# Database settings come ONLY from environment variables (Render dashboard).
# No credentials live in this file or in the repository.
: "${DB_CONNECTION:?Set DB_CONNECTION in the environment}"
: "${DB_HOST:?Set DB_HOST in the environment}"
: "${DB_DATABASE:?Set DB_DATABASE in the environment}"
: "${DB_USERNAME:?Set DB_USERNAME in the environment}"
: "${DB_PASSWORD:?Set DB_PASSWORD in the environment}"

# Process environment variables override .env. A .env file is only needed to store APP_KEY.
if [ ! -f /var/www/.env ]; then
    cp /var/www/.env.example /var/www/.env
fi
if [ -z "$APP_KEY" ] && ! grep -q "^APP_KEY=base64:" /var/www/.env; then
    php artisan key:generate --force
fi

chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
chmod -R 775 /var/www/storage /var/www/bootstrap/cache

php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

php artisan migrate --force
php artisan db:seed --force || true   # reference lookups only (idempotent)

php artisan config:cache
php artisan route:cache
php artisan view:cache

# First admin is created deliberately from the Render shell:
#   php artisan users:create-super-admin
service nginx start
exec php-fpm
