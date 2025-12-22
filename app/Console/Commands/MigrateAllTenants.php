<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate {--force}';
    protected $description = 'Comando universal y seguro para ejecutar migraciones pendientes en todos los tenants.';

    public function handle()
    {
        if (!$this->option('force')) {
            if (!$this->confirm('¿Ejecutar migraciones en TODOS los inquilinos?')) {
                return self::SUCCESS;
            }
        }

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando Tenant: {$tenant->name} ---");

            try {
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->db_database);
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                Config::set('database.connections.tenant.port', $tenant->db_port ?? 5432);

                DB::purge('tenant');
                DB::reconnect('tenant');

                /** @var \Illuminate\Database\Migrations\Migrator $migrator */
                $migrator = app('migrator');
                $migrator->setConnection('tenant');

                // --- BLOQUE CORREGIDO: Try-Catch Silencioso ---
                if (!Schema::connection('tenant')->hasTable('migrations')) {
                    try {
                        $migrator->getRepository()->createRepository();
                        $this->line('-> Tabla de migraciones creada.');
                    } catch (\Exception $e) {
                        // Código 42P07 es "Duplicate Table" en PostgreSQL.
                        // Si es ese error, lo ignoramos porque significa que la tabla ya está lista.
                        if ($e->getCode() !== '42P07') {
                            $this->warn("Nota: " . $e->getMessage());
                        }
                    }
                }
                // ----------------------------------------------

                $migrationsPath = database_path('migrations/tenant');
                $migrator->run([$migrationsPath], ['pretend' => false, 'step' => false]);

                $this->info("-> OK.");

            } catch (\Exception $e) {
                // Errores reales de conexión o migración sí los mostramos
                $this->error("Error crítico en {$tenant->name}: " . $e->getMessage());
            }
        }

        $this->info('¡Migración de tenants completada!');
        return self::SUCCESS;
    }
}