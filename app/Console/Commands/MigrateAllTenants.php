<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    /**
     * La firma del comando. Así lo llamaremos.
     */
    protected $signature = 'tenants:migrate-custom';

    protected $description = 'Ejecuta las migraciones para todos los tenants de forma manual y explícita.';

    public function handle()
    {
        $this->info('Iniciando migración personalizada para todos los tenants...');

        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants para migrar.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->info("--- Tenant: {$tenant->name} (ID: {$tenant->id}) ---");
            $this->line("Cambiando a la base de datos: {$tenant->getDatabaseName()}");

            // Purga la conexión 'tenant' para asegurar que no hay datos viejos
            DB::purge('tenant');

            // Establece la configuración de la base de datos para este tenant
            Config::set('database.connections.tenant.host', $tenant->db_host);
            Config::set('database.connections.tenant.database', $tenant->getDatabaseName());
            Config::set('database.connections.tenant.username', $tenant->db_username);
            Config::set('database.connections.tenant.password', $tenant->db_password);
            
            // Llama al comando 'migrate' de Laravel con parámetros explícitos
            // ¡ESTA ES LA PARTE MÁS IMPORTANTE!
            $this->call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations/tenant',
                '--force' => true,
            ]);

            $this->info("Migración completada para {$tenant->name}.");
            $this->line('');
        }

        $this->info('¡Migración personalizada de todos los tenants completada!');
        return 0;
    }
}