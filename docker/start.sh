#!/bin/sh
set -e # Salir si hay un error

echo "Forzando la eliminación de .env para leer variables del sistema..."
rm -f /var/www/html/.env

# --- INICIO DE LA CORRECCIÓN DE ALMACENAMIENTO (ADAPTADO) ---
# 1. Define dónde está montado el disco de Render (TU RUTA ACTUAL)
RENDER_DISK_STORAGE_PATH="/var/www/html/storage"

# 2. ASEGURARNOS DE QUE LAS SUBDIRECTORIOS ESENCIALES EXISTAN DENTRO DEL DISCO MONTADO
#    Usamos mkdir -p para crearlas solo si no existen.
echo "Asegurando directorios de storage en el disco montado..."
mkdir -p ${RENDER_DISK_STORAGE_PATH}/app/public/contratos_firmados
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/cache/data
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/sessions
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/views
mkdir -p ${RENDER_DISK_STORAGE_PATH}/logs

# 3. Asegura los permisos correctos DENTRO DEL DISCO MONTADO
echo "Asegurando permisos en el disco montado..."
chown -R www-data:www-data ${RENDER_DISK_STORAGE_PATH}
chmod -R 775 ${RENDER_DISK_STORAGE_PATH}

# 4. Ejecuta storage:link DESPUÉS de asegurar los directorios y permisos
#    Esto creará /var/www/html/public/storage -> /var/www/html/storage/app/public
echo "Linking storage public directory..."
php artisan storage:link || echo "Storage link already exists or failed, continuing..." # Añadimos '|| true' para no detener el script si el link ya existe
# --- FIN DE LA CORRECCIÓN DE ALMACENAMIENTO ---

echo "Running database migrations for central DB..."
php artisan migrate --force --no-interaction

echo "Clearing old caches..."
php artisan optimize:clear

echo "Caching new configuration..."
php artisan config:cache
php artisan route:cache
# php artisan view:cache # Comentado por precaución

# --- PASO DE DEPURACIÓN FINAL ---
echo "-----------------------------------------------------"
echo "VERIFICANDO EL ARCHIVO DE CACHÉ DE CONFIGURACIÓN GENERADO:"
cat bootstrap/cache/config.php | grep "'default' =>" || echo "Cache config not found or grep failed."
echo "-----------------------------------------------------"

echo "Substituting Nginx config..."
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

echo "Starting services..."
php-fpm &
nginx -g 'daemon off;'