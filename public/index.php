<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| Cargar Configuración de Entorno Dinámica (Multi-Tenant)
|--------------------------------------------------------------------------
|
| Aquí detectamos el dominio de la solicitud y cargamos el archivo .env
| correspondiente para la empresa (inquilino) correcta.
|
*/

// 1. Definimos el mapa de dominios a archivos .env
// !! IMPORTANTE: Usa aquí los dominios REALES que configurarás en Ngrok !!
$domainEnvMap = [
    // Dominios de producción/Ngrok
    'credintegra.tunel-ngrok.io' => '.env.credintegra', // <--- CAMBIA ESTO por tu dominio real de Ngrok
    'facturame.tunel-ngrok.io'   => '.env.facturame',   // <--- CAMBIA ESTO por tu dominio real de Ngrok

    // Dominios para desarrollo local
    'credintegra.localhost'      => '.env.credintegra',
    'facturame.localhost'        => '.env.facturame',
];

// 2. Obtenemos el host de la petición actual
$httpHost = $_SERVER['HTTP_HOST'] ?? null;

// 3. Determinamos qué archivo .env usar
// Si no se encuentra un dominio en el mapa, usará el archivo .env por defecto como respaldo.
$envFileToLoad = $domainEnvMap[$httpHost] ?? '.env';

// 4. Cargamos el archivo .env específico usando el componente Dotenv de Laravel
try {
    $dotenv = Dotenv\Dotenv::createImmutable(
        __DIR__.'/..', // <--- LA SOLUCIÓN
        $envFileToLoad
    );
    $dotenv->load();
} catch (\Dotenv\Exception\InvalidPathException $e) {
    // Manejar el error si el archivo .env no se encuentra
    die('El archivo de entorno (' . $envFileToLoad . ') no se encontró. Error: ' . $e->getMessage());
}


/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| (Esta parte del archivo ya existe, déjala como está)
|
*/
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
