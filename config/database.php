<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
    // Conexión para la base de datos CENTRAL (landlord)
    'pgsql' => [
        'driver'         => 'pgsql',
        'url'            => env('DATABASE_URL'),
        'host'           => env('DB_HOST'),
        'port'           => env('DB_PORT'),
        'database'       => env('DB_DATABASE'),
        'username'       => env('DB_USERNAME'),
        'password'       => env('DB_PASSWORD'),
        'charset'        => 'utf8',
        'prefix'         => '',
        'search_path'    => 'public',
        'sslmode'        => 'prefer',
        // Le decimos que sus migraciones están en la carpeta por defecto 'migrations'
        'migrations'     => 'migrations', 
    ],


        // Plantilla para las bases de datos de TENANTS (inquilinos)
        'tenant' => [
        'driver'         => 'pgsql',
        'host'           => null,
        'port'           => null,
        'database'       => null,
        'username'       => null,
        'password'       => null,
        'charset'        => 'utf8',
        'prefix'         => '',
        'search_path'    => 'public',
        'sslmode'        => 'prefer',
        // Le decimos que sus migraciones están en 'migrations/tenant'
        'migrations'     => database_path('migrations/tenant'),
    ],
    ],

    'migrations' => 'migrations',

    'redis' => [
        'client' => env('REDIS_CLIENT', 'phpredis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_').'_database_'),
        ],
        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
        ],
        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
        ],
    ],
];