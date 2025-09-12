<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar; // Importar Spatie PermissionRegistrar

class SeedMultiTenantPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:seed-tenants {--tenant=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds permissions for all or a specific tenant database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Define las bases de datos de tus tenants.
        // Asegúrate de que estos nombres coincidan con los de tus .env
        $tenantDatabases = [
            'prueba' => 'prueba_db',
            'credintegra' => 'credintegra_db',
            'facturame' => 'facturame_db',
            // Agrega más tenants si tienes
        ];

        // Obtiene el tenant específico si se ha proporcionado la opción --tenant
        $specificTenant = $this->option('tenant');

        if ($specificTenant && !array_key_exists($specificTenant, $tenantDatabases)) {
            $this->error("El tenant '{$specificTenant}' no está definido en la lista de tenants.");
            return Command::FAILURE;
        }

        foreach ($tenantDatabases as $tenantName => $databaseName) {
            // Si se especificó un tenant, solo procesa ese
            if ($specificTenant && $specificTenant !== $tenantName) {
                continue;
            }

            $this->info("Procesando tenant: <info>{$tenantName}</info> (Base de Datos: <comment>{$databaseName}</comment>)");

            try {
                // Paso 1: Configurar la conexión a la base de datos del tenant
                // Guarda la configuración actual de la DB 'mysql' para restaurarla al final
                $originalDbConfig = Config::get('database.connections.mysql');
                Config::set('database.connections.mysql.database', $databaseName);

                // Purga y reconecta la conexión por defecto 'mysql' para que use la DB del tenant
                DB::purge('mysql');
                DB::reconnect('mysql');
                $this->info("Conectado a {$databaseName}.");

                // Paso 2: Limpiar la caché de Spatie Permissions para esta conexión
                app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
                $this->comment("Caché de permisos de Spatie borrada.");


                // Paso 3: Ejecutar el seeder de permisos para esta base de datos
                // No uses el argumento --class directamente en $this->call(),
                // solo el nombre de la clase sin el namespace completo
                $this->call('db:seed', [
                    '--class' => 'Database\\Seeders\\PermissionSeeder', // Usa el FQCN aquí
                ]);

                $this->info("Seeding de PermissionSeeder completado para <info>{$tenantName}</info>.");

            } catch (\Exception $e) {
                $this->error("Error al seedear el tenant {$tenantName}: " . $e->getMessage());
                // Puedes agregar más manejo de errores o logging aquí
                // Opcional: Revertir la conexión en caso de error si no se hace en finally
            } finally {
                // Paso 4: Restaurar la configuración original de la base de datos (opcional pero buena práctica)
                // Esto asegura que la conexión por defecto vuelva a apuntar al `.env` principal
                Config::set('database.connections.mysql', $originalDbConfig);
                DB::purge('mysql');
                DB::reconnect('mysql');
            }

            if ($specificTenant) {
                break; // Si solo se pidió un tenant, sal del bucle
            }
        }

        $this->info("Proceso de seeding de permisos multi-tenant finalizado.");

        // Después de todo, limpia las cachés de la aplicación para que los cambios se reflejen en la web
        $this->info("Limpiando cachés de la aplicación...");
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');
        $this->call('route:clear'); // Si las rutas dependen de los permisos

        $this->info("Cachés de la aplicación limpiadas. Deberías ver los cambios en la interfaz de usuario.");

        return Command::SUCCESS;
    }
}
