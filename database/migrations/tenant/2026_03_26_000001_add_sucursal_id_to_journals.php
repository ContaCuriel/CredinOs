<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Detectar el esquema real según el nombre de la BD
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        // 2. Ejecutar el SQL apuntando al esquema detectado para agregar las columnas
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".journals ADD COLUMN IF NOT EXISTS sucursal_id BIGINT NULL;");
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".journals ADD COLUMN IF NOT EXISTS user_id BIGINT NULL;");
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        // Revertir en caso de ser necesario
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".journals DROP COLUMN IF EXISTS sucursal_id;");
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".journals DROP COLUMN IF EXISTS user_id;");
    }
};