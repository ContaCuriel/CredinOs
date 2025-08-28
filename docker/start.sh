#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

# Usar envsubst para reemplazar ${PORT} en nuestra plantilla de Nginx
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Running database migrations for central DB..."
php artisan migrate --force

# --- CORRECCIÓN DEFINITIVA ---
# Ejecutamos automáticamente las migraciones para cada inquilino al iniciar.
# Esto asegura que sus bases de datos siempre estén listas.
echo "Running migrations for tenants..."
php artisan tenant:migrate 1
php artisan tenant:migrate 2

echo "Caching configuration..."
php artisan config:cache
php artisan route:cache

echo "Linking storage directory..."
php artisan storage:link

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano
nginx -g 'daemon off;'
