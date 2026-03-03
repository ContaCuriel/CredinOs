<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('horarios', function (Blueprint $table) {
            // Activar o desactivar las reglas avanzadas para este horario
            $table->boolean('aplicar_reglas_avanzadas')->default(false);

            // Tolerancia y Retardos Menores
            $table->integer('tolerancia_minutos')->nullable()->comment('Ej. 10 (hasta las 8:10 no pasa nada)');
            $table->integer('retardo_menor_minutos_inicio')->nullable()->comment('Ej. 11');
            $table->integer('retardo_menor_minutos_fin')->nullable()->comment('Ej. 15');
            $table->integer('retardos_para_falta')->nullable()->comment('Ej. 3 retardos = 1 falta');

            // Medio Día y Faltas
            $table->integer('medio_dia_minutos_inicio')->nullable()->comment('Ej. 16');
            $table->integer('medio_dia_minutos_fin')->nullable()->comment('Ej. 30 (Descuenta medio día)');
            $table->integer('falta_minutos_inicio')->nullable()->comment('Ej. 31 (Se regresa a casa, cuenta como falta)');

            // Multiplicadores de Castigo por Falta Injustificada
            $table->decimal('castigo_falta_lun_vie', 8, 2)->nullable()->comment('Ej. 3 (descuento triple)');
            $table->decimal('castigo_falta_mar_jue_sab', 8, 2)->nullable()->comment('Ej. 2 (descuento doble)');
        });
    }

    public function down()
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropColumn([
                'aplicar_reglas_avanzadas',
                'tolerancia_minutos',
                'retardo_menor_minutos_inicio',
                'retardo_menor_minutos_fin',
                'retardos_para_falta',
                'medio_dia_minutos_inicio',
                'medio_dia_minutos_fin',
                'falta_minutos_inicio',
                'castigo_falta_lun_vie',
                'castigo_falta_mar_jue_sab'
            ]);
        });
    }
};