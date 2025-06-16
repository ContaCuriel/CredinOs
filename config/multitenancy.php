<?php

use App\Models\Tenant; // Asegúrate de tener este modelo
use Spatie\Multitenancy\TenantFinder\DomainTenantFinder;
use Spatie\Multitenancy\Tasks\SwitchTenantDatabaseTask;
use Spatie\Multitenancy\TenantDatabaseNames\DatabaseNameFromTenant; // <--- Añade esta importación

return [



/*
 * La ruta donde se encuentran las migraciones de los tenants.
 */
    'tenant_database_connection_name' => 'tenant',

/*
 * El nombre de la tabla de migraciones de los tenants.
 */
'tenant_migrations_table' => 'migrations',

/*
 * La ruta donde se encuentran las migraciones del landlord.
 */
'landlord_migrations_path' => database_path('migrations/landlord'), // La nueva carpeta
    
'current_tenant_database_name_strategy' => DatabaseNameFromTenant::class,
    
    /*
     * El nombre de la conexión a la base de datos del landlord.
     * Debe coincidir con el nombre en config/database.php
     */
    'landlord_database_connection_name' => 'landlord',

    /*
     * El nombre de la conexión a la base de datos de los tenants.
     * Debe coincidir con el nombre en config/database.php
     */
    'tenant_database_connection_name' => 'tenant',

    /*
     * Esta clase determina el tenant actual para la petición.
     * DomainTenantFinder es perfecto para tu caso de uso con subdominios.
     */
    //'tenant_finder' => DomainTenantFinder::class,

    /*
     * El nombre del atributo en tu modelo `Tenant` que contiene el subdominio.
     */
    //'domain_key' => 'subdominio',

    /*
     * Estas tareas se ejecutan al cambiar de tenant. La más importante
     * es SwitchTenantDatabaseTask, que es la que cambia la conexión a la BD.
     * DEBE ESTAR HABILITADA.
     */
    'switch_tenant_tasks' => [
        SwitchTenantDatabaseTask::class,
        // Puedes añadir otras tareas aquí si las necesitas, como:
        // \Spatie\Multitenancy\Tasks\PrefixCacheTask::class,
    ],

    /*
     * El modelo que usas para almacenar la información de tus tenants (empresas).
     */
    'tenant_model' => Tenant::class,

    /*
     * El resto de la configuración puede quedarse con sus valores por defecto
     * para empezar.
     */
    'queues_are_tenant_aware_by_default' => true,

    'tenant_artisan_search_fields' => [
        'id',
    ],

    // ... (el resto de las opciones por defecto)
];