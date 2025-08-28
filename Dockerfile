# Stage 1: Usar una imagen de Laravel Sail que ya incluye Composer y todas las dependencias.
# Esto elimina la necesidad de una construcción de varias etapas y la instalación manual de extensiones.
FROM laravelsail/php83-composer:latest as builder

# Instalar dependencias del sistema necesarias para Nginx y PostgreSQL
RUN apt-get update && apt-get install -y \
    nginx \
    postgresql-client \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

# Copiar los archivos de la aplicación
COPY . .

# Instalar dependencias de Composer
RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs

# Generar el autoloader optimizado y descubrir paquetes
RUN composer dump-autoload --no-dev --optimize

# Stage 2: Preparar la imagen final de producción
FROM php:8.3-fpm-bullseye

WORKDIR /var/www/html

# Instalar solo las dependencias de sistema necesarias para ejecutar la aplicación
RUN apt-get update && apt-get install -y \
        nginx \
        libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar los archivos de la aplicación y las dependencias de la etapa de construcción
COPY --from=builder /var/www/html .
COPY --from=builder /usr/bin/composer /usr/bin/composer

# Copiar el archivo de configuración de Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN rm /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configurar permisos correctos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Copia el script de inicio y dale permisos de ejecución
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# El contenedor ejecutará este script cuando inicie
CMD ["/usr/local/bin/start.sh"]