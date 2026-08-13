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

        // Le quitamos el candado de NOT NULL a la columna id_sucursal
        $sql = "ALTER TABLE \"$schema\".clientes ALTER COLUMN id_sucursal DROP NOT NULL";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        // Si nos arrepentimos, se lo volvemos a poner
        $sql = "ALTER TABLE \"$schema\".clientes ALTER COLUMN id_sucursal SET NOT NULL";

        DB::connection('tenant')->statement($sql);
    }
};