# Stage 1: Instalar dependencias con Composer
FROM composer:2 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install \
    --ignore-platform-reqs \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --prefer-dist

# Stage 2: Preparar la aplicación final de producción
FROM php:8.3-fpm-alpine as app

WORKDIR /var/www/html

# Instalar dependencias del sistema: Nginx y librerías de PostgreSQL
RUN apk add --no-cache nginx postgresql-dev

# Instalar extensiones de PHP
RUN docker-php-ext-install pdo pdo_pgsql

# Copiar el archivo de configuración de Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copiar archivos de la aplicación
COPY . .
COPY --from=vendor /app/vendor/ vendor/

# Copia el .env de producción
COPY .env.production .env

# Generar caché de configuración
RUN php artisan config:cache

# Configurar permisos
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Comando para iniciar el servidor
CMD sh -c "php-fpm & nginx -g 'daemon off;'"