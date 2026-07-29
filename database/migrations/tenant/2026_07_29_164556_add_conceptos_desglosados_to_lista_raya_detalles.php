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

        $sql = "ALTER TABLE \"$schema\".lista_raya_detalles 
                ADD COLUMN IF NOT EXISTS bono_permanencia NUMERIC(10,2) DEFAULT 0,
                ADD COLUMN IF NOT EXISTS bono_cumpleanos NUMERIC(10,2) DEFAULT 0,
                ADD COLUMN IF NOT EXISTS prima_vacacional NUMERIC(10,2) DEFAULT 0,
                ADD COLUMN IF NOT EXISTS deduccion_prestamo NUMERIC(10,2) DEFAULT 0,
                ADD COLUMN IF NOT EXISTS deduccion_caja_ahorro NUMERIC(10,2) DEFAULT 0,
                ADD COLUMN IF NOT EXISTS deduccion_infonavit NUMERIC(10,2) DEFAULT 0";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".lista_raya_detalles 
                DROP COLUMN IF EXISTS bono_permanencia,
                DROP COLUMN IF EXISTS bono_cumpleanos,
                DROP COLUMN IF EXISTS prima_vacacional,
                DROP COLUMN IF EXISTS deduccion_prestamo,
                DROP COLUMN IF EXISTS deduccion_caja_ahorro,
                DROP COLUMN IF EXISTS deduccion_infonavit";

        DB::connection('tenant')->statement($sql);
    }
};