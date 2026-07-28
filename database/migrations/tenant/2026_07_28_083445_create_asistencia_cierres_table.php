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

        // 1. Crear la tabla
        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".asistencia_cierres (
            id_asistencia_cierre BIGSERIAL PRIMARY KEY,
            id_empleado BIGINT NOT NULL,
            id_sucursal BIGINT NOT NULL,
            periodo VARCHAR(255) NOT NULL,
            faltas NUMERIC(5,2) DEFAULT 0,
            retardos INTEGER DEFAULT 0,
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP
        )";
        DB::connection('tenant')->statement($sql);

        // 2. Crear los índices para que la nómina se procese rapidísimo
        DB::connection('tenant')->statement("CREATE INDEX IF NOT EXISTS idx_asist_cierres_per_suc ON \"$schema\".asistencia_cierres (periodo, id_sucursal)");
        DB::connection('tenant')->statement("CREATE INDEX IF NOT EXISTS idx_asist_cierres_emp ON \"$schema\".asistencia_cierres (id_empleado)");
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "DROP TABLE IF EXISTS \"$schema\".asistencia_cierres CASCADE";

        DB::connection('tenant')->statement($sql);
    }
};