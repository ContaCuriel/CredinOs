<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class ClearTenantPermissionCache extends Command
{
    protected $signature = 'tenants:clear-permission-cache';
    protected $description = 'Limpia la caché de permisos de Spatie para todos los tenants.';

    public function handle(PermissionRegistrar $permissionRegistrar)
    {
        $tenants = Tenant::all();
        if ($tenants->isEmpty()) {
            $this->info('No hay tenants que limpiar.');
            return 0;
        }

        $this->info('Limpiando caché de permisos para todos los tenants...');
        $originalConnection = DB::getDefaultConnection(); // Guarda la conexión original

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando tenant: {$tenant->name} ---");
            try {
                // Configurar la conexión del tenant
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->db_database);
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                Config::set('database.connections.tenant.port', $tenant->db_port ?? 5432);

                DB::purge('tenant');

                // Establecer esta como la conexión por defecto para la operación de caché
                DB::setDefaultConnection('tenant');

                // Limpiar la caché de Spatie. Esto ahora operará en la BD del tenant.
                $permissionRegistrar->forgetCachedPermissions();

                $this->info("Caché de permisos de Spatie borrada para {$tenant->name}.");

            } catch (\Exception $e) {
                $this->error("Error limpiando caché para {$tenant->name}: " . $e->getMessage());
            }
        }

        // Restaurar la conexión por defecto al final
        DB::setDefaultConnection($originalConnection);
        $this->info('Cachés de tenants limpiadas.');
        return 0;
    }
}