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

        // 1. Configurar la conexión de la base de datos dinámicamente
        Config::set('database.connections.tenant_migration', [
            'driver'    => 'pgsql',
            'host'      => $tenant->db_host,
            'port'      => $tenant->db_port,
            'database'  => $tenant->db_database,
            'username'  => $tenant->db_username,
            'password'  => $tenant->db_password,
            'charset'   => 'utf8',
            'prefix'    => '',
            'schema'    => 'public', // Se mantiene para consistencia
            'sslmode'   => 'require', // Se asegura de usar SSL
        ]);
        
        // --- INICIO DE LA CORRECCIÓN CLAVE ---
        // 2. Establecer esta conexión como la predeterminada para el Facade DB
        DB::setDefaultConnection('tenant_migration');

        // 3. Forzar el camino de búsqueda (search_path) al esquema 'public'
        // Esta es la instrucción que resuelve el problema de "tabla duplicada".
        DB::statement('SET search_path TO public');
        // --- FIN DE LA CORRECCIÓN CLAVE ---

        $this->info("Running migrations for tenant: {$tenant->name}...");

        // 4. Ejecutar el comando de migración en la conexión ya configurada
        $this->call('migrate', [
            '--database' => 'tenant_migration',
            '--path' => 'database/migrations',
            '--force' => true, 
        ]);

        $this->info("Migrations for tenant {$tenant->name} completed successfully.");
    }
}