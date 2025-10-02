<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MigrateAllTenants extends Command
{
    /**
     * Renombramos el comando para que su propósito sea claro.
     */
    protected $signature = 'tenants:apply-schema-fix';

    protected $description = 'Aplica todas las correcciones de esquema pendientes directamente con SQL.';

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

                // 1. Asegurar que la tabla `migrations` exista
                $connection->statement("
                    CREATE TABLE IF NOT EXISTS migrations (
                        id SERIAL PRIMARY KEY,
                        migration VARCHAR(255) NOT NULL,
                        batch INT NOT NULL
                    );
                ");
                $this->info("-> Tabla 'migrations' asegurada.");


                // 2. Aplicar TODOS los cambios a la tabla 'clientes' de una vez
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

                // 3. Crear la tabla de referencias si no existe
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

                // 4. Convertir la columna 'vencimiento_ine' a AÑO (INTEGER)
                // Este es el único paso que podría fallar si ya se corrió, por eso el try-catch
                try {
                    DB::statement('ALTER TABLE clientes ALTER COLUMN vencimiento_ine TYPE INTEGER USING EXTRACT(YEAR FROM vencimiento_ine)');
                    $this->info("-> Columna 'vencimiento_ine' convertida a AÑO.");
                } catch (\Illuminate\Database\QueryException $e) {
                    // Si falla porque ya es integer, es un "error bueno". Lo ignoramos.
                    if (str_contains($e->getMessage(), 'column "vencimiento_ine" is of type integer')) {
                        $this->warn("-> La columna 'vencimiento_ine' ya era de tipo INTEGER. Saltando.");
                    } else {
                        throw $e; // Si es otro error, sí lo lanzamos.
                    }
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