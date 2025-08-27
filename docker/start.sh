#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

echo "Running database migrations..."
# Ejecuta las migraciones en la base de datos CENTRAL
php artisan migrate --force

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano (esto mantiene el contenedor activo)
nginx -g 'daemon off;'
