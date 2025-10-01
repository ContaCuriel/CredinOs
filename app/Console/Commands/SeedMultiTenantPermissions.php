<?php

namespace App\Console\Commands;

use App\Models\Tenant; // Importa el modelo Tenant
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class SeedMultiTenantPermissions extends Command
{
    protected $signature = 'db:seed-tenants {--tenant_id=}'; // Cambiado a --tenant_id
    protected $description = 'Seeds permissions for all or a specific tenant database.';

    public function handle()
    {
        // 1. Obtener los tenants de la base de datos central
        $specificTenantId = $this->option('tenant_id');

        if ($specificTenantId) {
            $tenants = Tenant::where('id', $specificTenantId)->get();
            if ($tenants->isEmpty()) {
                $this->error("No se encontró ningún inquilino con ID: {$specificTenantId}.");
                return Command::FAILURE;
            }
        } else {
            $tenants = Tenant::all(); // Obtener todos los tenants
            if ($tenants->isEmpty()) {
                $this->error("No hay inquilinos configurados en la base de datos central.");
                return Command::FAILURE;
            }
        }

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando inquilino: <info>{$tenant->name}</info> (ID: {$tenant->id}, DB: <comment>{$tenant->db_database}</comment>) ---");

            try {
                // Configurar la conexión dinámica para el inquilino actual
                // Asumimos que la plantilla de conexión para inquilinos se llama 'tenant'
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->db_database);
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                Config::set('database.connections.tenant.port', $tenant->db_port ?? 5432); // Usar puerto del tenant o por defecto 5432

                // Purga y reconecta la conexión 'tenant'
                DB::purge('tenant');
                DB::reconnect('tenant');
                $this->info("Conectado a la base de datos del inquilino: {$tenant->db_database} en {$tenant->db_host}.");

                // Limpiar la caché de Spatie Permissions para esta conexión
                app()->make(PermissionRegistrar::class)->forgetCachedPermissions();
                $this->comment("Caché de permisos de Spatie borrada para este inquilino.");

                // Ejecutar el seeder de permisos para esta base de datos del inquilino
                $this->call('db:seed', [
                    '--class' => 'Database\\Seeders\\PermissionSeeder',
                    '--database' => 'tenant', // Usar la conexión 'tenant' que acabamos de configurar
                ]);

                $this->info("Seeding de PermissionSeeder completado para <info>{$tenant->name}</info>.");

            } catch (\Exception $e) {
                $this->error("Error al seedear el inquilino {$tenant->name} (ID: {$tenant->id}): " . $e->getMessage());
                // No detenemos el proceso si un inquilino falla, continuamos con los demás
            }
        }

        $this->info("Proceso de seeding de permisos multi-tenant finalizado.");

        // Después de todo, limpia las cachés de la aplicación principal para que los cambios se reflejen
        $this->info("Limpiando cachés de la aplicación principal...");
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('view:clear');
        $this->call('route:clear');

        $this->info("Cachés de la aplicación principal limpiadas.");

        return Command::SUCCESS;
    }
}