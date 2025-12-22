<?php

namespace App\Console\Commands;

use App\Models\Tenant; // Importa el modelo Tenant
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class SeedMultiTenantPermissions extends Command
{
    protected $signature = 'db:seed-tenants {--tenant_id=} {--force}';
    protected $description = 'Seeds permissions for all or a specific tenant database.';

    public function handle()
    {
        // 1. Seguridad: Confirmar si no es force
        if (!$this->option('tenant_id') && !$this->option('force')) {
            if (!$this->confirm('¿Estás seguro de que deseas ejecutar los seeders en TODOS los inquilinos?')) {
                $this->info('Operación cancelada.');
                return self::SUCCESS;
            }
        }

        // Definimos si forzamos la ejecución (true si pasaste --force o si estás en local)
        $force = $this->option('force') ? true : false;

        $query = Tenant::query();
        if ($this->option('tenant_id')) {
            $query->where('id', $this->option('tenant_id'));
        }
        $tenants = $query->get();

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando inquilino: {$tenant->name} ---");

            try {
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->db_database);
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                Config::set('database.connections.tenant.port', $tenant->db_port ?? 5432);

                DB::purge('tenant');

                $this->comment("Conectado a la DB: {$tenant->db_database}");

                // --- AQUÍ ESTABA EL ERROR ---
                // Pasamos explícitamente '--force' => true al comando hijo
                $this->call('db:seed', [
                    '--class' => 'Database\\Seeders\\PermissionSeeder',
                    '--database' => 'tenant',
                    '--force' => $force 
                ]);

                // Limpiar caché
                $this->call('tenants:clear-permission-cache', [
                    '--tenant_id' => $tenant->id
                ]);

                $this->info("Seeding completado para {$tenant->name}.");

            } catch (\Exception $e) {
                $this->error("Error en {$tenant->name}: " . $e->getMessage());
            }
        }

        $this->info('Proceso finalizado.');
        return self::SUCCESS;
    }
}