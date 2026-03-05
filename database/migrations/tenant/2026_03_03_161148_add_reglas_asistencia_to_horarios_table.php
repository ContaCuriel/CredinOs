<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Detectamos el nombre de la base de datos actual para decidir el esquema
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = 'public'; // Valor por defecto

        if (str_contains($dbName, 'credintegra')) {
            $schema = 'credintegra_db';
        } elseif (str_contains($dbName, 'crediticia')) {
            $schema = 'facturame_db';
        }

        // Ejecutamos el SQL con el esquema detectado
        DB::connection('tenant')->statement("
            ALTER TABLE \"$schema\".horarios 
            ADD COLUMN IF NOT EXISTS aplicar_reglas_avanzadas BOOLEAN DEFAULT FALSE,
            ADD COLUMN IF NOT EXISTS tolerancia_minutos INTEGER,
            ADD COLUMN IF NOT EXISTS retardo_menor_minutos_inicio INTEGER,
            ADD COLUMN IF NOT EXISTS retardo_menor_minutos_fin INTEGER,
            ADD COLUMN IF NOT EXISTS retardos_para_falta INTEGER,
            ADD COLUMN IF NOT EXISTS medio_dia_minutos_inicio INTEGER,
            ADD COLUMN IF NOT EXISTS medio_dia_minutos_fin INTEGER,
            ADD COLUMN IF NOT EXISTS falta_minutos_inicio INTEGER,
            ADD COLUMN IF NOT EXISTS castigo_falta_lun_vie DECIMAL(8, 2),
            ADD COLUMN IF NOT EXISTS castigo_falta_mar_jue_sab DECIMAL(8, 2)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $dbName = DB::connection('tenant')->getDatabaseName();
        $schema = 'public';

        if (str_contains($dbName, 'credintegra')) {
            $schema = 'credintegra_db';
        } elseif (str_contains($dbName, 'crediticia')) {
            $schema = 'facturame_db';
        }

        DB::connection('tenant')->statement("
            ALTER TABLE \"$schema\".horarios 
            DROP COLUMN IF EXISTS aplicar_reglas_avanzadas,
            DROP COLUMN IF EXISTS tolerancia_minutos,
            DROP COLUMN IF EXISTS retardo_menor_minutos_inicio,
            DROP COLUMN IF EXISTS retardo_menor_minutos_fin,
            DROP COLUMN IF EXISTS retardos_para_falta,
            DROP COLUMN IF EXISTS medio_dia_minutos_inicio,
            DROP COLUMN IF EXISTS medio_dia_minutos_fin,
            DROP COLUMN IF EXISTS falta_minutos_inicio,
            DROP COLUMN IF EXISTS castigo_falta_lun_vie,
            DROP COLUMN IF EXISTS castigo_falta_mar_jue_sab
        ");
    }
};