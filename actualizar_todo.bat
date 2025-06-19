@echo off
REM Este script actualiza las deducciones para cada entorno (.env).

echo ==========================================================
echo === INICIANDO ACTUALIZACION GENERAL DE DEDUCCIONES ===
echo ==========================================================
echo.

echo [1] Procesando Base de Datos Principal (.env)...
php artisan deducciones:actualizar
echo.

echo [2] Procesando Base de Datos de Credintegra (.env.credintegra)...
php artisan deducciones:actualizar --env=credintegra
echo.

echo [3] Procesando Base de Datos de Facturame (.env.facturame)...
php artisan deducciones:actualizar --env=facturame
echo.

REM Anade mas bloques como los de arriba para cada .env extra que tengas...

echo ==========================================================
echo === PROCESO FINALIZADO ===
echo ==========================================================
pause