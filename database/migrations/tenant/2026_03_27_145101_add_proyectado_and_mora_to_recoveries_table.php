<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // 1. Detectar el esquema real según el nombre de la BD
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');
                 
        // 2. Ejecutar el SQL apuntando al esquema detectado
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".recoveries ADD COLUMN cobro_proyectado NUMERIC(15,2) DEFAULT 0;");
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".recoveries ADD COLUMN mora_periodo NUMERIC(15,2) DEFAULT 0;");
    }

    public function down()
    {
        // 1. Detectar el esquema real
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');
                 
        // 2. Ejecutar el SQL de reversa
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".recoveries DROP COLUMN cobro_proyectado;");
        DB::connection('tenant')->statement("ALTER TABLE \"$schema\".recoveries DROP COLUMN mora_periodo;");
    }
};