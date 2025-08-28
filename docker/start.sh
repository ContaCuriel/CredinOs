#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

# Usar envsubst para reemplazar ${PORT} en nuestra plantilla de Nginx
# y crear el archivo de configuración final que Nginx usará.
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Running database migrations for central DB..."
php artisan migrate --force

# --- CORRECCIÓN CLAVE AQUÍ ---
# Se eliminan los comandos 'clear' redundantes. Los comandos 'cache' ya limpian antes de crear.
echo "Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Linking storage directory..."
# Crea el enlace simbólico para el almacenamiento de archivos.
php artisan storage:link

echo "Starting services..."
# Inicia PHP-FPM en segundo plano
php-fpm &

# Inicia Nginx en primer plano con el nuevo archivo de configuración
nginx -g 'daemon off;'
