<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateAllTenants extends Command
{
    protected $signature = 'tenants:apply-schema-fix';
    protected $description = 'Aplica todas las correcciones de esquema pendientes de forma segura e idempotente.';

    public function handle()
    {
        $this->info('Iniciando reparación de esquema para todos los tenants...');
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

                $this->line("-> Aplicando correcciones de esquema...");

                // 1. Asegurar tablas y columnas (estas operaciones son seguras de correr múltiples veces)
                $connection->statement("CREATE TABLE IF NOT EXISTS migrations (id SERIAL PRIMARY KEY, migration VARCHAR(255) NOT NULL, batch INT NOT NULL);");
                $this->info("-> Tabla 'migrations' asegurada.");

                $connection->statement("
                    ALTER TABLE clientes
                    ADD COLUMN IF NOT EXISTS fecha_nacimiento DATE NULL,
                    ADD COLUMN IF NOT EXISTS genero VARCHAR(20) NULL,
                    ADD COLUMN IF NOT EXISTS estado_nacimiento VARCHAR(100) NULL,
                    ADD COLUMN IF NOT EXISTS nacionalidad VARCHAR(100) DEFAULT 'Mexicana',
                    ADD COLUMN IF NOT EXISTS estado_civil VARCHAR(50) NULL,
                    ADD COLUMN IF NOT EXISTS numero_hijos INT DEFAULT 0,
                    ADD COLUMN IF NOT EXISTS dependientes_economicos INT DEFAULT 0,
                    ADD COLUMN IF NOT EXISTS fecha_comprobante_domicilio DATE NULL,
                    ADD COLUMN IF NOT EXISTS destino_credito VARCHAR(255) NULL,
                    ADD COLUMN IF NOT EXISTS telefono_fijo VARCHAR(255) NULL,
                    ADD COLUMN IF NOT EXISTS anios_domicilio INT NULL,
                    ADD COLUMN IF NOT EXISTS tipo_vivienda VARCHAR(255) NULL;
                ");
                $this->info("-> Columnas de la tabla 'clientes' aseguradas.");
                
                $connection->statement("
                    CREATE TABLE IF NOT EXISTS cliente_referencias (
                        id BIGSERIAL PRIMARY KEY,
                        cliente_id BIGINT NOT NULL REFERENCES clientes(id_cliente) ON DELETE CASCADE,
                        nombre_referencia VARCHAR(255) NOT NULL,
                        parentesco VARCHAR(255) NOT NULL,
                        telefono VARCHAR(255) NOT NULL,
                        created_at TIMESTAMP(0) NULL,
                        updated_at TIMESTAMP(0) NULL
                    );
                ");
                $this->info("-> Tabla 'cliente_referencias' asegurada.");

                // 2. Convertir 'vencimiento_ine' SÓLO SI es necesario
                $columnType = $connection->table('information_schema.columns')
                    ->where('table_schema', 'public')
                    ->where('table_name', 'clientes')
                    ->where('column_name', 'vencimiento_ine')
                    ->value('data_type');

                if ($columnType !== 'integer') {
                    $this->line("-> Convirtiendo 'vencimiento_ine' de {$columnType} a INTEGER...");
                    $connection->statement('ALTER TABLE clientes ALTER COLUMN vencimiento_ine TYPE INTEGER USING EXTRACT(YEAR FROM vencimiento_ine)');
                    $this->info("-> Columna 'vencimiento_ine' convertida a AÑO.");
                } else {
                    $this->warn("-> La columna 'vencimiento_ine' ya es de tipo INTEGER. Saltando conversión.");
                }

                $this->info("-> ¡Esquema corregido exitosamente para {$tenant->name}!");

            } catch (\Exception $e) {
                $this->error("Ocurrió un error en {$tenant->name}: " . $e->getMessage());
                Log::error("Error migrando tenant {$tenant->name}: " . $e->getMessage());
            }
        }
        $this->info('¡Proceso de reparación completado!');
    }
}