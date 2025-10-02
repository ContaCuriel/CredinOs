<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';
    protected $description = 'Ejecuta las migraciones para todos los tenants (versión simple y directa).';

    public function handle()
    {
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("--- Migrando Tenant: {$tenant->name} ---");

            // Configura dinámicamente la conexión 'tenant'
            Config::set('database.connections.tenant.host', $tenant->db_host);
            Config::set('database.connections.tenant.database', $tenant->getDatabaseName());
            Config::set('database.connections.tenant.username', $tenant->db_username);
            Config::set('database.connections.tenant.password', $tenant->db_password);

            // Importante: Purgamos la conexión DESPUÉS de establecer la nueva configuración
            DB::purge('tenant');

            // Llama al comando migrate con los parámetros explícitos
            $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);
        }
        $this->info('¡Migración completada!');
    }
}