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

        $sqlPeriodos = "CREATE TABLE IF NOT EXISTS \"$schema\".lista_raya_periodos (
            id_periodo_lista bigserial PRIMARY KEY,
            periodo_rango varchar(255) NOT NULL,
            id_sucursal bigint NULL,
            status_periodo varchar(255) NOT NULL DEFAULT 'Borrador',
            created_at timestamp(0) NULL,
            updated_at timestamp(0) NULL
        )";
        DB::connection('tenant')->statement($sqlPeriodos);

        $sqlDetalles = "CREATE TABLE IF NOT EXISTS \"$schema\".lista_raya_detalles (
            id_detalle_lista bigserial PRIMARY KEY,
            id_periodo_lista bigint NOT NULL,
            id_empleado bigint NOT NULL,
            sueldo_mensual_historico numeric(10, 2) NOT NULL DEFAULT 0.00,
            sueldo_diario_historico numeric(10, 2) NOT NULL DEFAULT 0.00,
            puesto_historico varchar(255) NULL,
            dias_periodo integer NOT NULL DEFAULT 15,
            faltas_directas integer NOT NULL DEFAULT 0,
            retardos_acumulados integer NOT NULL DEFAULT 0,
            faltas_por_retardos integer NOT NULL DEFAULT 0,
            descuento_por_faltas numeric(10, 2) NOT NULL DEFAULT 0.00,
            otras_deducciones numeric(10, 2) NOT NULL DEFAULT 0.00,
            percepciones_extra numeric(10, 2) NOT NULL DEFAULT 0.00,
            total_neto numeric(10, 2) NOT NULL DEFAULT 0.00,
            created_at timestamp(0) NULL,
            updated_at timestamp(0) NULL,
            CONSTRAINT fk_periodo_lista FOREIGN KEY (id_periodo_lista) REFERENCES \"$schema\".lista_raya_periodos (id_periodo_lista) ON DELETE CASCADE
        )";
        DB::connection('tenant')->statement($sqlDetalles);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".lista_raya_detalles CASCADE");
        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".lista_raya_periodos CASCADE");
    }
};