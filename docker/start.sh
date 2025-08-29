#!/bin/sh
set -e
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
echo "Running database migrations for central DB..."
php artisan migrate --force
echo "Linking storage directory..."
php artisan storage:link
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
echo "Starting services..."
php-fpm &
nginx -g 'daemon off;'