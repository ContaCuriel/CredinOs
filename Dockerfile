# --- SOLUCIÓN DEFINITIVA USANDO UN ENFOQUE DE DOS ETAPAS ---

# ETAPA 1: El Constructor
# Usamos la imagen de Sail para instalar las dependencias de Composer de forma confiable.
FROM laravelsail/php83-composer:latest as builder

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --optimize-autoloader --ignore-platform-reqs

# ETAPA 2: La Imagen Final de Producción
# Usamos una imagen oficial de PHP-FPM que es ligera y correcta para producción.
FROM php:8.3-fpm-bullseye

WORKDIR /var/www/html

# Instalar Nginx y las librerías de sistema necesarias
RUN apt-get update && apt-get install -y \
        nginx \
        libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar los archivos de la aplicación y las dependencias ya instaladas desde la etapa del constructor
COPY --from=builder /var/www/html .

# Generar los archivos de caché de Laravel
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Configurar los permisos correctos usando el usuario estándar 'www-data'
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 storage bootstrap/cache

# Copiar nuestra configuración personalizada de Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN rm /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Copiar nuestro script de inicio
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# El contenedor ejecutará este script cuando inicie
CMD ["/usr/local/bin/start.sh"]