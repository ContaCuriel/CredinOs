<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate {--force}';
    protected $description = 'Comando universal y seguro para ejecutar migraciones pendientes en todos los tenants.';

    public function handle()
    {
        // 1. Pedir confirmación si no se está forzando la ejecución
        if (!$this->option('force')) {
            if (!$this->confirm('¿Estás seguro de que deseas ejecutar las migraciones en TODOS los inquilinos?')) {
                $this->info('Operación de migración cancelada.');
                return self::SUCCESS;
            }
        }

        $tenants = Tenant::all();
        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants para migrar.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando Tenant: {$tenant->name} ---");

            try {
                // 2. Configurar la conexión dinámicamente
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->db_database);
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                Config::set('database.connections.tenant.port', $tenant->db_port ?? 5432);
                DB::purge('tenant');

                // 3. Preparar el migrador de Laravel
                /** @var Migrator $migrator */
                $migrator = app('migrator');
                $migrator->setConnection('tenant');

                // 4. Asegurar que la tabla de migraciones exista (Corrección para SQLSTATE[42P07])
                // Usamos createRepository() que es idempotente y más seguro que llamar a 'migrate:install'.
                if (! $migrator->repositoryExists()) {
                     $this->line('-> Tabla de migraciones no encontrada, creando...');
                     $migrator->getRepository()->createRepository();
                     $this->info('-> Tabla de migraciones creada.');
                }

                // 5. Ejecutar las migraciones pendientes desde la ruta correcta
                $migrationsPath = database_path('migrations/tenant');
                $this->line('-> Buscando y ejecutando migraciones pendientes...');
                
                // Pasamos la opción 'pretend' a false y 'step' a false para una ejecución normal.
                $migrator->run([$migrationsPath], ['pretend' => false, 'step' => false]);

                $this->info("-> Migración finalizada para {$tenant->name}.");

            } catch (\Exception $e) {
                $this->error("Error procesando tenant {$tenant->name}: " . $e->getMessage());
            }
        }
        $this->info('¡Migración de tenants completada!');
    }
}