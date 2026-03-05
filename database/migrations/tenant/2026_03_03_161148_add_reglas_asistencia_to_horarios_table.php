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
        // Detectamos el esquema dinámicamente desde la conexión actual del inquilino
        $schema = config('database.connections.tenant.schema');

        // Usamos DB::statement para forzar la alteración en el esquema correcto (evitando las vistas en public)
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

        // Agregamos los comentarios a las columnas para mantener la documentación
        DB::connection('tenant')->statement("COMMENT ON COLUMN \"$schema\".horarios.tolerancia_minutos IS 'Ej. 10 (hasta las 8:10 no pasa nada)'");
        DB::connection('tenant')->statement("COMMENT ON COLUMN \"$schema\".horarios.retardos_para_falta IS 'Ej. 3 retardos = 1 falta'");
        DB::connection('tenant')->statement("COMMENT ON COLUMN \"$schema\".horarios.medio_dia_minutos_fin IS 'Descuenta medio día'");
        DB::connection('tenant')->statement("COMMENT ON COLUMN \"$schema\".horarios.falta_minutos_inicio IS 'Se regresa a casa, cuenta como falta'");
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $schema = config('database.connections.tenant.schema');

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