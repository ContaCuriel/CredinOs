<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';

    protected $description = 'Comando universal y seguro para ejecutar migraciones pendientes en todos los tenants.';

    public function handle()
    {
        $this->info('Iniciando migración universal para todos los tenants...');
        $tenants = Tenant::all();

        if ($tenants->isEmpty()) {
            $this->warn('No se encontraron tenants.');
            return 0;
        }

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando Tenant: {$tenant->name} ---");

            // 1. Configurar la conexión dinámicamente
            Config::set('database.connections.tenant.host', $tenant->db_host);
            Config::set('database.connections.tenant.database', $tenant->getDatabaseName());
            Config::set('database.connections.tenant.username', $tenant->db_username);
            Config::set('database.connections.tenant.password', $tenant->db_password);
            DB::purge('tenant');

            // 2. Preparar el migrador de Laravel
            /** @var Migrator $migrator */
            $migrator = app('migrator');
            $migrator->setConnection('tenant'); // Usar la conexión del tenant

            // 3. Asegurar que la tabla de migraciones exista (de forma segura)
            if (! $migrator->repositoryExists()) {
                 $this->line('Tabla de migraciones no encontrada, creando...');
                 $this->call('migrate:install', ['--database' => 'tenant']);
            }

            // 4. Ejecutar las migraciones pendientes desde la ruta correcta
            $migrationsPath = database_path('migrations/tenant');
            $this->line('Buscando y ejecutando migraciones pendientes...');
            
            // El método run() es público y se encarga de todo el proceso de forma segura.
            $migrator->run([$migrationsPath]);

            // 5. Imprimir los resultados
            $notes = $migrator->getNotes();
            if (empty($notes)) {
                $this->info('No había migraciones nuevas que ejecutar.');
            } else {
                foreach ($notes as $note) {
                    $this->info(strip_tags($note)); // Limpiamos la salida para que sea legible
                }
            }
        }

        $this->info('¡Migración universal de tenants completada!');
    }
}