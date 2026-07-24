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

        $sql = "CREATE TABLE IF NOT EXISTS \"$schema\".configuracion_nomina (
            id bigserial PRIMARY KEY,
            retardos_para_falta integer NOT NULL DEFAULT 3,
            descontar_septimo_dia boolean NOT NULL DEFAULT true,
            metodo_calculo_dias varchar(255) NOT NULL DEFAULT 'fijos_15',
            pagar_dia_31 varchar(255) NOT NULL DEFAULT 'nadie',
            redondear_neto boolean NOT NULL DEFAULT true,
            created_at timestamp(0) NULL,
            updated_at timestamp(0) NULL
        )";

        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        DB::connection('tenant')->statement("DROP TABLE IF EXISTS \"$schema\".configuracion_nomina CASCADE");
    }
};