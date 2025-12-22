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
                
                // Reconexión explícita para asegurar que Schema use la conexión correcta
                DB::reconnect('tenant'); 

                /** @var \Illuminate\Database\Migrations\Migrator $migrator */
                $migrator = app('migrator');
                $migrator->setConnection('tenant');

                // --- CORRECCIÓN: Usar Schema facade es más seguro ---
                if (!Schema::connection('tenant')->hasTable('migrations')) {
                     $this->line('-> Creando tabla de migraciones...');
                     $migrator->getRepository()->createRepository();
                }

                $migrationsPath = database_path('migrations/tenant');
                $migrator->run([$migrationsPath], ['pretend' => false, 'step' => false]);

                $this->info("-> OK.");

            } catch (\Exception $e) {
                $this->error("Error en {$tenant->name}: " . $e->getMessage());
            }
        }

        $this->info('¡Migración de tenants completada!');
        return self::SUCCESS;
    }
}