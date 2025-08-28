# --- SOLUCIÓN DEFINITIVA USANDO UNA IMAGEN PRE-CONSTRUIDA DE LARAVEL ---

# Stage 1: Usar la imagen oficial de Laravel Sail que ya incluye Composer y todas las extensiones.
FROM laravelsail/php83-composer:latest

# Establecer el directorio de trabajo
WORKDIR /var/www/html

# Instalar Nginx, que usaremos como nuestro servidor web
RUN apt-get update && apt-get install -y nginx && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copiar todos los archivos de nuestra aplicación al contenedor
COPY . .

# --- CORRECCIÓN CLAVE AQUÍ ---
# Instalar las dependencias de Composer ignorando los requisitos de la plataforma para máxima compatibilidad.
RUN composer install --no-dev --no-interaction --optimize-autoloader --ignore-platform-reqs

# Generar los archivos de caché de Laravel para un rendimiento óptimo
RUN php artisan config:cache
RUN php artisan route:cache
RUN php artisan view:cache

# Configurar los permisos correctos para las carpetas de Laravel
# El usuario por defecto en la imagen de Sail es 'sail'
RUN chown -R sail:sail /var/www/html
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