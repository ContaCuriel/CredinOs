<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migrator;

class MigrateAllTenants extends Command
{
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

            // Importante: Nos aseguramos de que Laravel use la conexión correcta
            DB::reconnect('tenant');

            // ¡LA SOLUCIÓN!
            // En lugar de llamar al comando 'migrate' que tiene pasos extra,
            // usamos directamente el servicio 'migrator' de Laravel.
            
            /** @var Migrator $migrator */
            $migrator = app('migrator');
            
            // Le decimos al migrator que use la base de datos del tenant
            $migrator->setConnection('tenant');

            // Verificamos si la tabla de migraciones existe, si no, la crea.
            if (! $migrator->repositoryExists()) {
                $this->call('migrate:install', ['--database' => 'tenant']);
            }

            // Ejecutamos las migraciones pendientes desde la ruta correcta.
            $migrator->run([database_path('migrations/tenant')], ['pretend' => false, 'step' => false]);
            
            // Imprimimos las notas/logs de las migraciones que se corrieron.
            foreach ($migrator->getNotes() as $note) {
                $this->output->writeln($note);
            }

            $this->info("Migración completada para {$tenant->name}.");
            $this->line('');
        }

        $this->info('¡Migración personalizada de todos los tenants completada!');
        return 0;
    }
}