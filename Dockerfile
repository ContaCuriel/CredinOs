# Stage 1: Instalar dependencias con Composer
FROM composer:2 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
# Añadimos --no-scripts para que Composer solo instale las dependencias sin ejecutar nada más
RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs --no-scripts

# Stage 2: Preparar la aplicación final de producción
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# --- CORRECCIÓN CLAVE AQUÍ ---
# Se instalan las dependencias del sistema y las extensiones de PHP en un solo bloque
# para garantizar que las librerías estén disponibles.
RUN apk add --no-cache \
        nginx \
        postgresql-dev \
        oniguruma-dev \
        libxml2-dev \
    && docker-php-ext-install \
        pdo pdo_pgsql \
        bcmath \
        ctype \
        fileinfo \
        mbstring \
        tokenizer \
        xml \
        openssl

# Copiar el archivo de configuración de Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copiar archivos de la aplicación
COPY . .
COPY --from=vendor /app/vendor/ vendor/

# Ahora que tenemos todos los archivos, generamos el autoloader optimizado.
RUN composer dump-autoload --no-dev --optimize

# Configurar permisos correctos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Copia el script de inicio y dale permisos de ejecución
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# El contenedor ejecutará este script cuando inicie
CMD ["/usr/local/bin/start.sh"]