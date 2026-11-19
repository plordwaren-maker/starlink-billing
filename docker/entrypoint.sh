#!/usr/bin/env sh
set -eu

cd /var/www/html

PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: Set APP_KEY in the Render Environment settings before starting."
    exit 1
fi

echo "Caching Laravel configuration..."
php artisan config:cache

echo "Running database migrations..."
php artisan migrate --force

if [ -n "${ADMIN_EMAIL:-}" ]; then
    echo "Checking initial administrator account..."
    php artisan billing:bootstrap-admin
else
    echo "WARNING: ADMIN_EMAIL is empty; no initial administrator was created."
fi

exec apache2-foreground
