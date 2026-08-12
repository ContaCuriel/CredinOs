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

        $sql = "ALTER TABLE \"$schema\".clientes 
                ADD COLUMN IF NOT EXISTS mismo_domicilio_laboral BOOLEAN DEFAULT FALSE,
                ADD COLUMN IF NOT EXISTS calle_negocio VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS numero_negocio VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS colonia_negocio VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS codigo_postal_negocio VARCHAR(10) NULL,
                ADD COLUMN IF NOT EXISTS municipio_negocio VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS estado_negocio VARCHAR(255) NULL,
                ADD COLUMN IF NOT EXISTS estatus VARCHAR(50) DEFAULT 'prospecto'";
        
        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".clientes 
                DROP COLUMN IF EXISTS mismo_domicilio_laboral,
                DROP COLUMN IF EXISTS calle_negocio,
                DROP COLUMN IF EXISTS numero_negocio,
                DROP COLUMN IF EXISTS colonia_negocio,
                DROP COLUMN IF EXISTS codigo_postal_negocio,
                DROP COLUMN IF EXISTS municipio_negocio,
                DROP COLUMN IF EXISTS estado_negocio,
                DROP COLUMN IF EXISTS estatus";

        DB::connection('tenant')->statement($sql);
    }
};