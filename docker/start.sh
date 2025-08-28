#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

# Usar envsubst para reemplazar ${PORT} en nuestra plantilla de Nginx
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Running database migrations for central DB..."
php artisan migrate --force

echo "Running migrations for tenants..."
# Ejecutamos las migraciones para cada inquilino ANTES de cualquier otra cosa.
php artisan tenant:migrate 1
php artisan tenant:migrate 2

echo "Linking storage directory..."
php artisan storage:link

# --- CORRECCIÓN CLAVE AQUÍ ---
# Movemos la creación de la caché al final, justo antes de iniciar los servicios.
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano
nginx -g 'daemon off;'
