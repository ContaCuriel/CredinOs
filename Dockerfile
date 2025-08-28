# Stage 1: Instalar dependencias con Composer
FROM composer:2 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-dev --no-interaction --prefer-dist

# Stage 2: Preparar la aplicación final de producción
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Instalar dependencias del sistema: Nginx y librerías de PostgreSQL
RUN apk add --no-cache nginx postgresql-dev

# -----------------------------------------------------------------
# CORRECCIÓN CLAVE: Instalar TODAS las extensiones de PHP que Laravel necesita.
# -----------------------------------------------------------------
RUN docker-php-ext-install pdo pdo_pgsql bcmath ctype fileinfo mbstring tokenizer xml openssl

# Copiar el archivo de configuración de Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copiar archivos de la aplicación
COPY . .
COPY --from=vendor /app/vendor/ vendor/

# Configurar permisos correctos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Copia el script de inicio y dale permisos de ejecución
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# El contenedor ejecutará este script cuando inicie
CMD ["/usr/local/bin/start.sh"]
