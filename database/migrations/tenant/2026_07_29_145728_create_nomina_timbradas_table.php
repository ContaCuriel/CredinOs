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
        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".nominas_timbradas (
            id_nomina_timbrada BIGSERIAL PRIMARY KEY,
            id_detalle_lista BIGINT NOT NULL,
            id_empleado BIGINT NOT NULL,
            
            sueldo_bruto NUMERIC(10,2) NOT NULL,
            isr_retenido NUMERIC(10,2) DEFAULT 0,
            imss_retenido NUMERIC(10,2) DEFAULT 0,
            
            estado_timbrado VARCHAR(20) DEFAULT 'pendiente',
            uuid_cfdi VARCHAR(255) NULL,
            xml_path VARCHAR(255) NULL,
            pdf_path VARCHAR(255) NULL,
            mensaje_error_sat TEXT NULL,
            
            created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

            CONSTRAINT fk_nomina_timbrada_detalle FOREIGN KEY (id_detalle_lista) REFERENCES \"$schema\".lista_raya_detalles(id_detalle_lista) ON DELETE CASCADE,
            CONSTRAINT fk_nomina_timbrada_empleado FOREIGN KEY (id_empleado) REFERENCES \"$schema\".empleados(id_empleado) ON DELETE CASCADE
        )";
        DB::connection('tenant')->statement($sql);

        // 2. Crear los índices para búsquedas ultra rápidas al timbrar
        DB::connection('tenant')->statement("CREATE INDEX IF NOT EXISTS idx_nom_timb_detalle ON \"$schema\".nominas_timbradas (id_detalle_lista)");
        DB::connection('tenant')->statement("CREATE INDEX IF NOT EXISTS idx_nom_timb_empleado ON \"$schema\".nominas_timbradas (id_empleado)");
        DB::connection('tenant')->statement("CREATE INDEX IF NOT EXISTS idx_nom_timb_estado ON \"$schema\".nominas_timbradas (estado_timbrado)");
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "DROP TABLE IF EXISTS \"$schema\".nominas_timbradas CASCADE";

        DB::connection('tenant')->statement($sql);
    }
};