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

        // --- INICIO DE LA CORRECCIÓN CLAVE ---
        // 1. Purgar cualquier conexión en caché para asegurar que se use la nueva configuración.
        DB::purge('tenant_migration');

        // 2. Configurar la conexión de la base de datos dinámicamente.
        // La clave 'schema' => 'public' le dice a Laravel que establezca el search_path.
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
            'sslmode'   => 'require',
        ]);
        // --- FIN DE LA CORRECCIÓN CLAVE ---

        $this->info("Running migrations for tenant: {$tenant->name}...");

        // 3. Ejecutar el comando de migración en la conexión del inquilino.
        // El comando 'migrate' de Laravel usará la configuración que acabamos de definir.
        $this->call('migrate', [
            '--database' => 'tenant_migration',
            '--path' => 'database/migrations',
            '--force' => true, 
        ]);

        $this->info("Migrations for tenant {$tenant->name} completed successfully.");
    }
}