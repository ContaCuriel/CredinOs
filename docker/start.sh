#!/bin/sh
set -e 

echo "--- INICIANDO DESPLIEGUE DE CREDINOS ---"

echo "1. Forzando la eliminación de .env..."
rm -f /var/www/html/.env

# --- STORAGE (Igual que antes) ---
RENDER_DISK_STORAGE_PATH="/var/www/html/storage"
# ... (tus comandos de mkdir y chown van aquí, no cambian) ...
echo "Linking storage public..."
php artisan storage:link || echo "Storage link ya existe..."

# --- LIMPIEZA PREVIA (Seguridad para que las migraciones no fallen) ---
# Mantenemos esto aquí para asegurar que las migraciones corran con config fresca
php artisan optimize:clear

# --- MIGRACIONES Y SEEDS ---
echo "4. Ejecutando migraciones CENTRALES..."
php artisan migrate --force --no-interaction

echo "5. Ejecutando migraciones de INQUILINOS..."
php artisan tenants:migrate --force

echo "6. Sembrando datos de INQUILINOS..."
php artisan db:seed-tenants --force

# --- OPTIMIZACIÓN FINAL (Aquí aplicamos la sugerencia de Gemini) ---
echo "7. Optimizando aplicación para producción..."
# ESTO REEMPLAZA A config:cache y route:cache
php artisan optimize

echo "8. Limpiando caché de permisos de tenants..."
php artisan tenants:clear-permission-cache

# --- SERVICIOS ---
echo "9. Arrancando servicios..."
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf
php-fpm &
nginx -g 'daemon off;'