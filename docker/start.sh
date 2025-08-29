#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

# Usar envsubst para reemplazar ${PORT} en nuestra plantilla de Nginx
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Running database migrations for central DB..."
php artisan migrate --force

echo "Linking storage directory..."
php artisan storage:link

# --- CORRECCIÓN DE PERMISOS Y CACHÉ ---
echo "Ensuring cache directories exist and have correct permissions..."
# 1. Asegurarse de que los directorios de caché existan
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data

# 2. Asignar los permisos correctos al usuario del servidor web (www-data)
chown -R www-data:www-data storage bootstrap/cache

# 3. Establecer permisos de escritura para el propietario y el grupo.
chmod -R 775 storage bootstrap/cache

# 4. Limpiar cualquier caché antigua que pueda causar conflictos.
php artisan view:clear
php artisan config:clear

echo "Caching configuration for production..."
php artisan config:cache
php artisan route:cache

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano
nginx -g 'daemon off;'