#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

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

# --- CORRECCIÓN CLAVE AQUÍ ---
echo "Clearing old caches to read new environment variables..."
# Limpia cualquier caché de configuración antigua para asegurar que se lean las nuevas variables de Redis.
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Caching new configuration for production..."
php artisan config:cache
php artisan route:cache

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano
nginx -g 'daemon off;'