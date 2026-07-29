<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".lista_raya_detalles 
                ADD COLUMN IF NOT EXISTS deduccion_isr NUMERIC(10,2) DEFAULT 0,
                ADD COLUMN IF NOT EXISTS deduccion_imss NUMERIC(10,2) DEFAULT 0";
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".lista_raya_detalles 
                DROP COLUMN IF EXISTS deduccion_isr,
                DROP COLUMN IF EXISTS deduccion_imss";
        DB::connection('tenant')->statement($sql);
    }
};