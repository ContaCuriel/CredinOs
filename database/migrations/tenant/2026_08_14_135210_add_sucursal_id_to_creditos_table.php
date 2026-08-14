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

        $sql = "ALTER TABLE \"$schema\".creditos ADD COLUMN sucursal_id BIGINT NULL;
                ALTER TABLE \"$schema\".creditos ADD CONSTRAINT fk_credito_sucursal FOREIGN KEY (sucursal_id) REFERENCES \"$schema\".sucursales (id_sucursal) ON DELETE RESTRICT;";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".creditos DROP CONSTRAINT IF EXISTS fk_credito_sucursal;
                ALTER TABLE \"$schema\".creditos DROP COLUMN IF EXISTS sucursal_id;";
                
        DB::connection('tenant')->statement($sql);
    }
};