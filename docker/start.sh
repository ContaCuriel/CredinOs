#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

echo "Running database migrations..."
# Ejecuta las migraciones en la base de datos CENTRAL
php artisan migrate --force

# -----------------------------------------------------------------
# NUEVA LÍNEA: Crea el enlace simbólico para el almacenamiento de archivos.
# Esto es necesario para que los archivos subidos al disco sean públicamente accesibles.
# -----------------------------------------------------------------
echo "Linking storage directory..."
php artisan storage:link

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano (esto mantiene el contenedor activo)
nginx -g 'daemon off;'