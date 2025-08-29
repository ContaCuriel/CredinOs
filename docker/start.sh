#!/bin/sh
set -e

echo "Forzando la eliminación de .env para leer variables del sistema..."
rm -f /var/www/html/.env

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

# --- PASO DE DEPURACIÓN FINAL ---
echo "-----------------------------------------------------"
echo "VERIFICANDO EL ARCHIVO DE CACHÉ DE CONFIGURACIÓN GENERADO:"
# Imprimimos la línea donde se define el driver de caché por defecto
cat bootstrap/cache/config.php | grep "'default' =>"
echo "-----------------------------------------------------"

echo "Starting services..."
php-fpm &
nginx -g 'daemon off;'