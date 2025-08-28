#!/bin/sh

# Salir inmediatamente si un comando falla
set -e

# Usar envsubst para reemplazar ${PORT} en nuestra plantilla de Nginx
# y crear el archivo de configuración final que Nginx usará.
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Running database migrations for central DB..."
php artisan migrate --force

# --- CORRECCIÓN CLAVE AQUÍ ---
# Limpiar todas las cachés antiguas antes de crear las nuevas para asegurar un estado limpio.
echo "Clearing old caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Caching new configuration..."
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

# Inicia Nginx en primer plano con el nuevo archivo de configuración
nginx -g 'daemon off;'
