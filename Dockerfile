# Stage 1: Instalar dependencias con Composer
FROM composer:2 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
# Añadimos --no-scripts para que Composer solo instale las dependencias sin ejecutar nada más
RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs --no-scripts

# Stage 2: Preparar la aplicación final de producción
FROM php:8.3-fpm-bullseye

WORKDIR /var/www/html

# --- CORRECCIÓN CLAVE AQUÍ ---
# Se separa la instalación de dependencias y extensiones para mayor robustez.

# 1. Instalar dependencias del sistema (excepto las de GD)
RUN apt-get update && apt-get install -y \
        nginx \
        build-essential \
        unzip \
        git \
        curl \
        libpq-dev \
        libonig-dev \
        libxml2-dev \
        libssl-dev \
        libzip-dev \
        zlib1g-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Instalar extensiones de PHP (excepto GD)
RUN docker-php-ext-install \
        pdo pdo_pgsql \
        bcmath \
        ctype \
        fileinfo \
        mbstring \
        tokenizer \
        xml \
        openssl \
        zip

# 3. Instalar dependencias del sistema SOLO para la extensión GD
RUN apt-get update && apt-get install -y \
        libpng-dev \
        libjpeg-dev \
        libfreetype6-dev \
        libwebp-dev \
        libxpm-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 4. Configurar e instalar la extensión GD de forma aislada
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd

# Copiar el archivo de configuración de Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN rm /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copiar archivos de la aplicación
COPY . .
COPY --from=vendor /app/vendor/ vendor/

# Generar el autoloader optimizado.
RUN composer dump-autoload --no-dev --optimize

# Preparar directorios y caché para producción
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache
RUN php artisan config:cache
RUN php artisan view:cache
RUN php artisan route:cache

# Copia el script de inicio y dale permisos de ejecución
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# El contenedor ejecutará este script cuando inicie
CMD ["/usr/local/bin/start.sh"]
