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

        $sql = "ALTER TABLE \"$schema\".empleados 
                ADD COLUMN IF NOT EXISTS nombre_fiscal VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS cp_fiscal VARCHAR(5) NULL,
                ADD COLUMN IF NOT EXISTS regimen_fiscal VARCHAR(5) DEFAULT '605'";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".empleados 
                DROP COLUMN IF EXISTS nombre_fiscal,
                DROP COLUMN IF EXISTS cp_fiscal,
                DROP COLUMN IF EXISTS regimen_fiscal";

        DB::connection('tenant')->statement($sql);
    }
};