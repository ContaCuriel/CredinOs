<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';
    protected $description = 'Ejecuta UNA migración específica de forma 100% manual y segura.';

    public function handle()
    {
        // ¡IMPORTANTE! Asegúrate de que este nombre sea el correcto para tu nueva migración
        $migrationFile = '2025_10_02_121319_ajustar_campos_clientes';

        $this->info("Iniciando ejecución manual para la migración: {$migrationFile}");
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando Tenant: {$tenant->name} ---");

            try {
                // Configura la conexión dinámicamente
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->getDatabaseName());
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                DB::purge('tenant');
                $connection = DB::connection('tenant');

                // Verificamos si la tabla de migraciones existe, si no, la creamos
                if (! $connection->getSchemaBuilder()->hasTable('migrations')) {
                    $this->info('-> Tabla de migraciones no existe, creando...');
                    $this->call('migrate:install', ['--database' => 'tenant']);
                }

                // Verificamos si la migración específica YA se ha ejecutado
                $ran = $connection->table('migrations')->where('migration', 'like', "%{$migrationFile}%")->exists();
                if ($ran) {
                    $this->info("-> La migración '{$migrationFile}' ya fue ejecutada. Saltando.");
                    continue;
                }

                $this->line("-> Ejecutando SQL para la migración '{$migrationFile}'...");

                // EJECUTAMOS EL SQL DE LA MIGRACIÓN DIRECTAMENTE
                $connection->statement('ALTER TABLE clientes ALTER COLUMN vencimiento_ine TYPE INTEGER USING EXTRACT(YEAR FROM vencimiento_ine)');
                $connection->statement("
                    ALTER TABLE clientes
                    ADD COLUMN IF NOT EXISTS telefono_fijo VARCHAR(255) NULL,
                    ADD COLUMN IF NOT EXISTS anios_domicilio INT NULL,
                    ADD COLUMN IF NOT EXISTS tipo_vivienda VARCHAR(255) NULL;
                ");
                $this->info("-> Columnas añadidas/modificadas en la tabla 'clientes'.");

                // Registramos la migración en la tabla 'migrations' para no volver a correrla
                $batch = $connection->table('migrations')->max('batch') + 1;
                $connection->table('migrations')->insert([
                    // Buscamos el nombre completo del archivo para ser exactos
                    'migration' => collect(\File::files(database_path('migrations/tenant')))
                                    ->first(fn($file) => str_contains($file->getFilename(), $migrationFile))
                                    ->getFilenameWithoutExtension(),
                    'batch' => $batch
                ]);
                $this->info("-> Migración registrada en la tabla 'migrations'. ¡Éxito!");

            } catch (\Exception $e) {
                $this->error("Ocurrió un error: " . $e->getMessage());
            }
        }
        $this->info('¡Proceso manual completado!');
    }
}