<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        // Conexión para la base de datos CENTRAL (landlord)
        'pgsql' => [
            'driver'         => 'pgsql',
            'url'            => env('DATABASE_URL'), // La forma más segura para Render
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', '5432'),
            'database'       => env('DB_DATABASE'),
            'username'       => env('DB_USERNAME'),
            'password'       => env('DB_PASSWORD'),
            'charset'        => 'utf8',
            'prefix'         => '',
            'search_path'    => 'public',
            'sslmode'        => 'prefer',
            'migrations'     => 'migrations', // Ruta por defecto para migraciones del landlord
        ],

        // Plantilla para las bases de datos de TENANTS (inquilinos)
        'tenant' => [
            'driver'         => 'pgsql',
            'host'           => null, // Se llena dinámicamente
            'port'           => null, // Se llena dinámicamente
            'database'       => null, // Se llena dinámicamente
            'username'       => null, // Se llena dinámicamente
            'password'       => null, // Se llena dinámicamente
            'charset'        => 'utf8',
            'prefix'         => '',
            'search_path'    => 'public',
            'sslmode'        => 'prefer',
            'migrations'     => database_path('migrations/tenant'), // Ruta para migraciones de tenants
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