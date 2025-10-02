<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';

    protected $description = 'Usa el migrator de Laravel directamente para evitar el bug de "duplicate table".';

    public function handle()
    {
        $this->info('Iniciando migración directa para todos los tenants...');

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("--- Tenant: {$tenant->name} ---");

            // Configura la conexión dinámicamente
            Config::set('database.connections.tenant.host', $tenant->db_host);
            Config::set('database.connections.tenant.database', $tenant->getDatabaseName());
            Config::set('database.connections.tenant.username', $tenant->db_username);
            Config::set('database.connections.tenant.password', $tenant->db_password);
            DB::purge('tenant');

            // Preparamos el migrador de Laravel
            $migrator = app('migrator');
            $migrator->setConnection('tenant'); // ¡Importante! Usar la conexión del tenant

            // Obtenemos la ruta de las migraciones
            $path = database_path('migrations/tenant');

            // Obtenemos las migraciones pendientes
            $pendingMigrations = $migrator->pendingMigrations($path, $migrator->getRepository()->getRan());

            if (empty($pendingMigrations)) {
                $this->info('No hay migraciones pendientes para ejecutar.');
                continue; // Pasa al siguiente tenant
            }

            // Ejecutamos solo las migraciones pendientes
            $this->line('Ejecutando migraciones pendientes...');
            $migrator->runPending($pendingMigrations);

            // Imprimimos los resultados
            foreach ($migrator->getNotes() as $note) {
                $this->info(strip_tags($note)); // strip_tags para limpiar la salida
            }
        }

        $this->info('¡Migración directa completada!');
    }
}