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

        $sql = "ALTER TABLE \"$schema\".patrones 
                ADD COLUMN IF NOT EXISTS registro_patronal VARCHAR(20) NULL,
                ADD COLUMN IF NOT EXISTS regimen_fiscal VARCHAR(5) NULL,
                ADD COLUMN IF NOT EXISTS codigo_postal VARCHAR(5) NULL,
                ADD COLUMN IF NOT EXISTS csd_cer_path VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS csd_key_path VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS csd_password VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS csd_expires_at TIMESTAMP NULL";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".patrones 
                DROP COLUMN IF EXISTS registro_patronal,
                DROP COLUMN IF EXISTS regimen_fiscal,
                DROP COLUMN IF EXISTS codigo_postal,
                DROP COLUMN IF EXISTS csd_cer_path,
                DROP COLUMN IF EXISTS csd_key_path,
                DROP COLUMN IF EXISTS csd_password,
                DROP COLUMN IF EXISTS csd_expires_at";

        DB::connection('tenant')->statement($sql);
    }
};