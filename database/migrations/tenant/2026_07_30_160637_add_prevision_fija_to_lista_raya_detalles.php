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

        // Agregamos la columna CURP (longitud 18, permitiendo nulos para las Personas Morales)
        $sql = "ALTER TABLE \"$schema\".patrones 
                ADD COLUMN IF NOT EXISTS curp VARCHAR(18) NULL";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".patrones 
                DROP COLUMN IF EXISTS curp";

        DB::connection('tenant')->statement($sql);
    }
};