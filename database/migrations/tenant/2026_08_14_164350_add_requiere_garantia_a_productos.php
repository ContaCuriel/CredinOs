<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        // 1. Agregamos la columna a tu esquema correcto
        $sql1 = "ALTER TABLE \"$schema\".productos_credito ADD COLUMN IF NOT EXISTS requiere_garantia BOOLEAN DEFAULT FALSE";
        DB::connection('tenant')->statement($sql1);

        // 2. Creamos la tabla de garantías
        $sql2 = "CREATE TABLE IF NOT EXISTS \"$schema\".credito_garantias (
            id SERIAL PRIMARY KEY,
            credito_id BIGINT NOT NULL,
            tipo_garantia VARCHAR(50) NOT NULL,
            
            vehiculo_documento VARCHAR(255) NULL,
            vehiculo_tipo VARCHAR(100) NULL,
            vehiculo_marca VARCHAR(100) NULL,
            vehiculo_modelo VARCHAR(150) NULL,
            vehiculo_anio VARCHAR(10) NULL,
            vehiculo_motor VARCHAR(100) NULL,
            vehiculo_color VARCHAR(100) NULL,
            vehiculo_serie VARCHAR(100) NULL,
            
            propiedad_documento VARCHAR(255) NULL,
            propiedad_ubicacion VARCHAR(255) NULL,
            propiedad_medidas TEXT NULL,
            propiedad_superficie VARCHAR(150) NULL,

            estatus_resguardo VARCHAR(50) DEFAULT 'En Bóveda Sucursal',
            ubicacion_fisica VARCHAR(255) NULL, 
            fecha_devolucion DATE NULL,
            notas_resguardo TEXT NULL,

            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            CONSTRAINT fk_garantia_credito FOREIGN KEY (credito_id) REFERENCES \"$schema\".creditos (id) ON DELETE CASCADE
        )";
        DB::connection('tenant')->statement($sql2);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        // Reversamos el cambio en tu esquema correcto
        $sql1 = "DROP TABLE IF EXISTS \"$schema\".credito_garantias CASCADE";
        DB::connection('tenant')->statement($sql1);

        $sql2 = "ALTER TABLE \"$schema\".productos_credito DROP COLUMN IF EXISTS requiere_garantia";
        DB::connection('tenant')->statement($sql2);
    }
};