# --- SOLUCIÓN DEFINITIVA USANDO UN ENFOQUE DE DOS ETAPAS ---

# ETAPA 1: El Constructor
FROM laravelsail/php83-composer:latest as builder

WORKDIR /var/www/html
COPY . .
RUN composer install --no-dev --no-interaction --optimize-autoloader --ignore-platform-reqs

# ETAPA 2: La Imagen Final de Producción
FROM php:8.3-fpm-bullseye

WORKDIR /var/www/html

# --- CORRECCIÓN CLAVE AQUÍ ---
# Instalar Nginx, las librerías de sistema y la utilidad 'gettext' para envsubst.
RUN apt-get update && apt-get install -y \
        nginx \
        libpq-dev \
        gettext \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar los archivos de la aplicación y las dependencias ya instaladas desde la etapa del constructor
COPY --from=builder /var/www/html .

# Configurar los permisos correctos usando el usuario estándar 'www-data'
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 775 storage bootstrap/cache

# Copiar nuestra configuración de Nginx (que ahora es una plantilla)
COPY docker/nginx.conf /etc/nginx/nginx.conf.template

# Copiar nuestro script de inicio
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# El contenedor ejecutará este script cuando inicie
CMD ["/usr/local/bin/start.sh"]