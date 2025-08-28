# Stage 1: Instalar dependencias con Composer
FROM composer:2 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
# --- CORRECCIÓN CLAVE AQUÍ ---
# Añadimos --no-scripts para que Composer solo instale las dependencias sin ejecutar nada más
RUN composer install --no-dev --no-interaction --prefer-dist --ignore-platform-reqs --no-scripts

# Stage 2: Preparar la aplicación final de producción
FROM php:8.3-fpm-alpine

WORKDIR /var/www/html

# Instalar dependencias del sistema: Nginx y librerías de PostgreSQL
RUN apk add --no-cache nginx postgresql-dev

# Instalar TODAS las extensiones de PHP que Laravel necesita
RUN docker-php-ext-install pdo pdo_pgsql bcmath ctype fileinfo mbstring tokenizer xml openssl

# Copiar el archivo de configuración de Nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Copiar archivos de la aplicación
COPY . .
COPY --from=vendor /app/vendor/ vendor/

# --- NUEVO PASO ---
# Ahora que tenemos todos los archivos, generamos el autoloader optimizado.
# Este comando también ejecuta el "package:discover" de forma segura.
RUN composer dump-autoload --no-dev --optimize

# Configurar permisos correctos para Laravel
RUN chown -R www-data:www-data storage bootstrap/cache
RUN chmod -R 775 storage bootstrap/cache

# Copia el script de inicio y dale permisos de ejecución
COPY docker/start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# El contenedor ejecutará este script cuando inicie
CMD ["/usr/local/bin/start.sh"]
