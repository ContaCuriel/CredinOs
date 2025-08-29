#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

# --- PASO DE DEPURACIÓN 1: ASEGURAR QUE NO HAYA .env ---
echo "Forzando la eliminación de .env para leer variables del sistema..."
rm -f /var/www/html/.env

# --- PASO DE DEPURACIÓN 2: MOSTRAR QUÉ VE LARAVEL ---
echo "-----------------------------------------------------"
echo "Laravel está viendo estas variables ANTES de la caché:"
php artisan tinker --execute="echo 'CACHE_DRIVER VISTO: ' . env('CACHE_DRIVER', '¡NO VISTO!');"
php artisan tinker --execute="echo 'SESSION_DRIVER VISTO: ' . env('SESSION_DRIVER', '¡NO VISTO!');"
php artisan tinker --execute="echo 'REDIS_URL VISTA: ' . env('REDIS_URL', '¡NO VISTA!');"
echo "-----------------------------------------------------"

# Usar envsubst para reemplazar ${PORT} en nuestra plantilla de Nginx
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Running database migrations for central DB..."
php artisan migrate --force

echo "Linking storage directory..."
php artisan storage:link

echo "Ensuring cache directories exist and have correct permissions..."
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Caching new configuration..."
php artisan config:cache
php artisan route:cache

echo "Starting services..."
php-fpm &
nginx -g 'daemon off;'