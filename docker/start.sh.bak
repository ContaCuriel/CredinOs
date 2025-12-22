#!/bin/sh
set -e # Salir si hay un error

echo "Forzando la eliminación de .env para leer variables del sistema..."
rm -f /var/www/html/.env

# --- CORRECCIÓN DE ALMACENAMIENTO (YA ESTABA BIEN) ---
RENDER_DISK_STORAGE_PATH="/var/www/html/storage"
echo "Asegurando directorios de storage en el disco montado..."
mkdir -p ${RENDER_DISK_STORAGE_PATH}/app/public/contratos_firmados
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/cache/data
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/sessions
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/views
mkdir -p ${RENDER_DISK_STORAGE_PATH}/logs
echo "Asegurando permisos en el disco montado..."
chown -R www-data:www-data ${RENDER_DISK_STORAGE_PATH}
chmod -R 775 ${RENDER_DISK_STORAGE_PATH}
echo "Linking storage public directory..."
php artisan storage:link || echo "Storage link already exists or failed, continuing..."
# --- FIN DE LA CORRECCIÓN DE ALMACENAMIENTO ---

echo "Running database migrations for central DB..."
php artisan migrate --force --no-interaction

echo "Clearing old caches..."
php artisan optimize:clear

echo "Caching new configuration..."
php artisan config:cache
php artisan route:cache

# --- ¡NUEVA LÍNEA AÑADIDA! ---
echo "Clearing tenant permission caches..."
php artisan tenants:clear-permission-cache
# --- FIN DE LA NUEVA LÍNEA ---

echo "-----------------------------------------------------"
echo "VERIFICANDO EL ARCHIVO DE CACHÉ DE CONFIGURACIÓN GENERADO:"
cat bootstrap/cache/config.php | grep "'default' =>" || echo "Cache config not found or grep failed."
echo "-----------------------------------------------------"

echo "Substituting Nginx config..."
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Starting services..."
php-fpm &
nginx -g 'daemon off;'