<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class MigrateTenant extends Command
{
    protected $signature = 'tenant:migrate {tenantId}';
    protected $description = 'Run migrations for a specific tenant';

    public function handle()
    {
        $tenantId = $this->argument('tenantId');
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            $this->error("Tenant with ID {$tenantId} not found.");
            return;
        }

        // Cambiar la conexión de la base de datos dinámicamente
        Config::set('database.connections.tenant_migration', [
            'driver'    => 'pgsql',
            'host'      => $tenant->db_host,
            'port'      => $tenant->db_port,
            'database'  => $tenant->db_database,
            'username'  => $tenant->db_username,
            'password'  => $tenant->db_password,
            'charset'   => 'utf8',
            'prefix'    => '',
            'schema'    => 'public',
            'sslmode'   => 'prefer',
        ]);

        $this->info("Running migrations for tenant: {$tenant->name}...");

        // Ejecutar el comando de migración en la conexión del inquilino
        $this->call('migrate', [
            '--database' => 'tenant_migration',
            '--path' => 'database/migrations',
            '--force' => true, // Necesario para producción
        ]);

        $this->info("Migrations for tenant {$tenant->name} completed successfully.");
    }
}
