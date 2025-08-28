#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

echo "Running database migrations for central DB..."
# Ejecuta las migraciones en la base de datos CENTRAL
php artisan migrate --force

echo "Caching configuration..."
# Genera la caché AHORA, con las variables de entorno correctas inyectadas por Render.
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Linking storage directory..."
# Crea el enlace simbólico para el almacenamiento de archivos.
php artisan storage:link

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano (esto mantiene el contenedor activo)
nginx -g 'daemon off;'
