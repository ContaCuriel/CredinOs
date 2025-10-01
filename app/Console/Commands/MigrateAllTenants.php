<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';

    protected $description = 'Ejecuta las migraciones para todos los tenants de forma segura, verificando si la tabla de migraciones existe.';

    public function handle()
    {
        $this->info('Iniciando migración personalizada y segura para todos los tenants...');

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

            // Importante: Nos aseguramos de que Laravel use la nueva configuración de conexión
            DB::reconnect('tenant');
            
            // Verificamos si la tabla de migraciones ya existe en esta base de datos de tenant
            $schema = DB::connection('tenant')->getSchemaBuilder();
            if (! $schema->hasTable('migrations')) {
                $this->line('La tabla de migraciones no existe. Creándola...');
                // Si no existe, ejecutamos el comando que SÓLO crea esa tabla.
                $this->call('migrate:install', ['--database' => 'tenant']);
            }

            // Ahora que estamos seguros de que la tabla 'migrations' existe,
            // ejecutamos las migraciones pendientes.
            $this->line('Buscando y ejecutando migraciones pendientes...');
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