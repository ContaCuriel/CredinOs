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

        // Agregamos la columna como booleana
        $sql = "ALTER TABLE \"$schema\".creditos ADD COLUMN IF NOT EXISTS descuenta_primer_pago BOOLEAN DEFAULT FALSE";
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        // Revertimos el cambio
        $sql = "ALTER TABLE \"$schema\".creditos DROP COLUMN IF NOT EXISTS descuenta_primer_pago";
        DB::connection('tenant')->statement($sql);
    }
};