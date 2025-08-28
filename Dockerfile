# Stage 1: Instalar dependencias con Composer
FROM composer:2 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
# Añadimos --no-scripts para que Composer solo instale las dependencias sin ejecutar nada más
RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs --no-scripts

# Stage 2: Preparar la aplicación final de producción
# Cambiamos la imagen base a una de Debian (Bullseye) que es más estable y completa.
FROM php:8.3-fpm-bullseye

WORKDIR /var/www/html

# --- CORRECCIÓN CLAVE AQUÍ ---
# Se separan las instalaciones para mayor robustez y mejor cacheo.
# 1. Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
        nginx \
        build-essential \
        unzip \
        git \
        curl \
        ghostscript \
        libpq-dev \
        libonig-dev \
        libxml2-dev \
        libssl-dev \
        libzip-dev \
        zlib1g-dev \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libmagickwand-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones de PHP
RUN pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo pdo_pgsql \
        bcmath \
        ctype \
        fileinfo \
        mbstring \
        tokenizer \
        xml \
        openssl \
        zip \
        gd

# Copiar el archivo de configuración de Nginx a la carpeta de sitios disponibles
COPY docker/nginx.conf /etc/nginx/sites-available/default
# Eliminar el enlace simbólico por defecto y crear el nuestro
RUN rm /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

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
