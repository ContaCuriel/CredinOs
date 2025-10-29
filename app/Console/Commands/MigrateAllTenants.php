<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate';
    protected $description = 'Comando universal y seguro para ejecutar migraciones pendientes en todos los tenants.';

    public function handle()
    {
        $tenants = Tenant::all();
        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants para migrar.');
            return;
        }

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando Tenant: {$tenant->name} ---");

            try {
                // 1. Configurar la conexión dinámicamente
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->getDatabaseName());
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                Config::set('database.connections.tenant.port', $tenant->db_port ?? 5432);
                DB::purge('tenant');

                // 2. Preparar el migrador de Laravel
                /** @var Migrator $migrator */
                $migrator = app('migrator');
                $migrator->setConnection('tenant');

                // 3. Asegurar que la tabla de migraciones exista
                if (! $migrator->repositoryExists()) {
                     $this->line('-> Tabla de migraciones no encontrada, creando...');
                     $this->call('migrate:install', ['--database' => 'tenant']);
                }

                // 4. Ejecutar las migraciones pendientes desde la ruta correcta
                $migrationsPath = database_path('migrations/tenant');
                $this->line('-> Buscando y ejecutando migraciones pendientes...');
                
                // Usamos run() para ejecutar las migraciones.
                $migrator->run([$migrationsPath]);

                // 5. Imprimir los resultados (FORMA CORREGIDA Y SEGURA)
                // Obtenemos las notas del array de salida del migrador
                $notes = $migrator->setOutput($this->output)->getNotes();
                if (empty($notes)) {
                    $this->info('-> No había migraciones nuevas que ejecutar.');
                } else {
                    // Si hubo notas (migraciones ejecutadas), las mostramos
                    foreach ($notes as $note) {
                        $this->info(strip_tags($note));
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error procesando tenant {$tenant->name}: " . $e->getMessage());
            }
        }
        $this->info('¡Migración de tenants completada!');
    }
}