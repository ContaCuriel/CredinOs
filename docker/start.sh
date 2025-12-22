#!/bin/sh
set -e # Salir si hay un error crítico

echo "--- INICIANDO DESPLIEGUE DE CREDINOS ---"

echo "1. Forzando la eliminación de .env para leer variables del sistema..."
rm -f /var/www/html/.env

# --- GESTIÓN DE ALMACENAMIENTO (CRÍTICO: NO TOCAR) ---
RENDER_DISK_STORAGE_PATH="/var/www/html/storage"
echo "2. Asegurando directorios y permisos de storage..."
mkdir -p ${RENDER_DISK_STORAGE_PATH}/app/public/contratos_firmados
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/cache/data
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/sessions
mkdir -p ${RENDER_DISK_STORAGE_PATH}/framework/views
mkdir -p ${RENDER_DISK_STORAGE_PATH}/logs

chown -R www-data:www-data ${RENDER_DISK_STORAGE_PATH}
chmod -R 775 ${RENDER_DISK_STORAGE_PATH}

echo "3. Linking storage public..."
php artisan storage:link || echo "Storage link ya existe o falló, continuando..."

# --- MIGRACIONES Y BASE DE DATOS ---
echo "4. Ejecutando migraciones CENTRALES..."
php artisan migrate --force --no-interaction

echo "5. Ejecutando migraciones de INQUILINOS (Tenants)..."
# Usamos tu comando personalizado
php artisan tenants:migrate --force

echo "6. Sembrando datos de INQUILINOS (Arreglo del Menú)..."
# ¡AQUÍ ESTÁ LA SOLUCIÓN AL MENÚ FANTASMA!
php artisan db:seed-tenants --force

# --- CACHÉ Y OPTIMIZACIÓN ---
echo "7. Limpiando y recacheando configuración..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache

echo "8. Limpiando caché de permisos de tenants..."
php artisan tenants:clear-permission-cache

# --- ARRANQUE DE SERVICIOS ---
echo "9. Configurando Nginx y arrancando servicios..."
envsubst '${PORT}' < /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Iniciar procesos
php-fpm &
nginx -g 'daemon off;'