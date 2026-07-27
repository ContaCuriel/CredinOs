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

        $sql = "ALTER TABLE \"$schema\".horarios 
                ADD COLUMN IF NOT EXISTS minutos_limite_retardo integer DEFAULT 15,
                ADD COLUMN IF NOT EXISTS retardos_por_falta integer DEFAULT 3,
                ADD COLUMN IF NOT EXISTS aplica_medio_dia boolean DEFAULT false,
                ADD COLUMN IF NOT EXISTS minutos_limite_medio_dia integer DEFAULT 30,
                ADD COLUMN IF NOT EXISTS aplica_castigo_multiplicador boolean DEFAULT false,
                ADD COLUMN IF NOT EXISTS multiplicador_lunes_viernes integer DEFAULT 1,
                ADD COLUMN IF NOT EXISTS multiplicador_dias_regulares integer DEFAULT 1";

        DB::connection('tenant')->statement($sql);
    }

    public function down(): void
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = str_contains($dbName, 'credintegra') ? 'credintegra_db' : 
                 (str_contains($dbName, 'crediticia') ? 'facturame_db' : 'public');

        $sql = "ALTER TABLE \"$schema\".horarios 
                DROP COLUMN IF EXISTS minutos_limite_retardo,
                DROP COLUMN IF EXISTS retardos_por_falta,
                DROP COLUMN IF EXISTS aplica_medio_dia,
                DROP COLUMN IF EXISTS minutos_limite_medio_dia,
                DROP COLUMN IF EXISTS aplica_castigo_multiplicador,
                DROP COLUMN IF EXISTS multiplicador_lunes_viernes,
                DROP COLUMN IF EXISTS multiplicador_dias_regulares";

        DB::connection('tenant')->statement($sql);
    }
};