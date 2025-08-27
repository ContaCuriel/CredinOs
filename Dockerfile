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


# Stage 2: Preparar la aplicación final
FROM php:8.3-fpm-alpine as app

# --- LA LÍNEA NUEVA Y CORREGIDA ESTÁ AQUÍ ---
# Instalar las librerías de desarrollo de PostgreSQL y luego las extensiones de PHP
RUN apk add --no-cache postgresql-dev && docker-php-ext-install pdo pdo_pgsql

# Copiar archivos de la aplicación y dependencias
COPY . .
COPY --from=vendor /app/vendor/ vendor/

# Configurar permisos para Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Exponer el puerto para el servidor web
EXPOSE 9000
CMD ["php-fpm"]