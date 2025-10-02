<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';

    protected $description = 'Ejecuta las migraciones para todos los tenants usando el método run() del migrador.';

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
            $migrator->setConnection('tenant'); // Usar la conexión del tenant

            // Si la tabla de migraciones no existe, el migrador la creará automáticamente.
            if (! $migrator->repositoryExists()) {
                 $this->line('Tabla de migraciones no encontrada, creando...');
                 $this->call('migrate:install', ['--database' => 'tenant']);
            }

            // Obtenemos la ruta de las migraciones
            $migrationsPath = database_path('migrations/tenant');

            // Ejecutamos las migraciones pendientes desde la ruta correcta.
            // El método run() es PÚBLICO y se encarga de todo el proceso.
            $this->line('Buscando y ejecutando migraciones pendientes...');
            $migrator->run([$migrationsPath]);

            // Imprimimos los resultados
            $notes = $migrator->getNotes();
            if (empty($notes)) {
                $this->info('No había migraciones nuevas que ejecutar.');
            } else {
                foreach ($notes as $note) {
                    // Usamos strip_tags para limpiar la salida y que se vea bien
                    $this->info(strip_tags($note));
                }
            }
        }

        $this->info('¡Migración directa completada!');
    }
}