#!/bin/sh

echo "Container en modo mantenimiento. Accediendo a la Shell para ejecutar migraciones..."

# Este comando mantiene el contenedor vivo para siempre sin iniciar Nginx o PHP-FPM.
sleep infinity