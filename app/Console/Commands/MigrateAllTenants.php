<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:migrate-custom';

    protected $description = 'Ejecuta UNA migración específica de forma manual y segura en todos los tenants.';

    public function handle()
    {
        // ¡IMPORTANTE! Revisa que este nombre coincida EXACTAMENTE con tu archivo de migración.
        $migrationFile = '2025_10_01_103420_update_clientes_and_create_referencias_table';

        $this->info("Iniciando ejecución manual y segura para la migración: {$migrationFile}");
        
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
                
                // PASO 1: VERIFICAR SI LA MIGRACIÓN YA SE EJECUTÓ
                $ran = $connection->table('migrations')->where('migration', $migrationFile)->exists();

                if ($ran) {
                    $this->info("-> La migración ya existe en este tenant. Saltando.");
                    continue; // Pasa al siguiente tenant
                }

                $this->line('-> La migración está pendiente. Ejecutando SQL manualmente...');

                // PASO 2: EJECUTAR EL SQL (NO DESTRUCTIVO)
                $connection->statement("
                    ALTER TABLE clientes
                    ADD COLUMN IF NOT EXISTS fecha_nacimiento DATE NULL,
                    ADD COLUMN IF NOT EXISTS genero VARCHAR(255) NULL,
                    ADD COLUMN IF NOT EXISTS vencimiento_ine DATE NULL,
                    ADD COLUMN IF NOT EXISTS estado_nacimiento VARCHAR(255) NULL,
                    ADD COLUMN IF NOT EXISTS nacionalidad VARCHAR(255) DEFAULT 'Mexicana',
                    ADD COLUMN IF NOT EXISTS estado_civil VARCHAR(255) NULL,
                    ADD COLUMN IF NOT EXISTS numero_hijos INT DEFAULT 0,
                    ADD COLUMN IF NOT EXISTS dependientes_economicos INT DEFAULT 0,
                    ADD COLUMN IF NOT EXISTS fecha_comprobante_domicilio DATE NULL,
                    ADD COLUMN IF NOT EXISTS destino_credito VARCHAR(255) NULL;
                ");
                $this->info("-> Columnas añadidas a la tabla 'clientes' (si no existían).");

                $connection->statement("
                    CREATE TABLE IF NOT EXISTS cliente_referencias (
                        id BIGSERIAL PRIMARY KEY,
                        cliente_id BIGINT NOT NULL,
                        nombre_referencia VARCHAR(255) NOT NULL,
                        parentesco VARCHAR(255) NOT NULL,
                        telefono VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
                        updated_at TIMESTAMP(0) WITHOUT TIME ZONE NULL,
                        CONSTRAINT fk_referencia_cliente
                            FOREIGN KEY(cliente_id)
                            REFERENCES clientes(id_cliente)
                            ON DELETE CASCADE
                    );
                ");
                $this->info("-> Tabla 'cliente_referencias' creada (si no existía).");

                // PASO 3: REGISTRAR LA MIGRACIÓN EN LA TABLA 'migrations'
                $batch = $connection->table('migrations')->max('batch') + 1;
                $connection->table('migrations')->insert([
                    'migration' => $migrationFile,
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