<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';
    protected $description = 'Aplica la migración de AJUSTAR CAMPOS CLIENTES de forma manual y segura.';

    public function handle()
    {
        // El nombre EXACTO del archivo de la SEGUNDA migración
        $migrationFile = '2025_10_02_121319_ajustar_campos_clientes';

        $this->info("Iniciando ejecución manual para: {$migrationFile}");
        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $this->info("--- Procesando Tenant: {$tenant->name} ---");

            try {
                // Configura la conexión
                Config::set('database.connections.tenant.host', $tenant->db_host);
                Config::set('database.connections.tenant.database', $tenant->getDatabaseName());
                Config::set('database.connections.tenant.username', $tenant->db_username);
                Config::set('database.connections.tenant.password', $tenant->db_password);
                DB::purge('tenant');
                $connection = DB::connection('tenant');

                // Verificamos si esta migración específica ya corrió
                $ran = $connection->table('migrations')->where('migration', 'like', "%{$migrationFile}%")->exists();
                if ($ran) {
                    $this->info("-> Migración '{$migrationFile}' ya fue ejecutada. Saltando.");
                    continue;
                }

                $this->line("-> Ejecutando SQL para la migración '{$migrationFile}'...");

                // EJECUTAMOS EL SQL DIRECTAMENTE
                $connection->statement('ALTER TABLE clientes ALTER COLUMN vencimiento_ine TYPE INTEGER USING EXTRACT(YEAR FROM vencimiento_ine)');
                $this->info("-> Columna 'vencimiento_ine' modificada.");

                $connection->statement("
                    ALTER TABLE clientes
                    ADD COLUMN IF NOT EXISTS telefono_fijo VARCHAR(255) NULL,
                    ADD COLUMN IF NOT EXISTS anios_domicilio INT NULL,
                    ADD COLUMN IF NOT EXISTS tipo_vivienda VARCHAR(255) NULL;
                ");
                $this->info("-> Columnas nuevas añadidas.");

                // Registramos la migración en la tabla 'migrations'
                $batch = $connection->table('migrations')->max('batch') + 1;
                $migrationFullName = collect(\File::files(database_path('migrations/tenant')))
                                    ->first(fn($file) => str_contains($file->getFilename(), $migrationFile))
                                    ->getFilenameWithoutExtension();
                
                $connection->table('migrations')->insert(['migration' => $migrationFullName, 'batch' => $batch]);
                $this->info("-> Migración '{$migrationFullName}' registrada. ¡Éxito!");

            } catch (\Exception $e) {
                $this->error("Ocurrió un error: " . $e->getMessage());
            }
        }
        $this->info('¡Proceso manual completado!');
    }
}