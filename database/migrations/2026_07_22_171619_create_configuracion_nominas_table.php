<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_nomina', function (Blueprint $table) {
            $table->id();
            $table->integer('retardos_para_falta')->default(3);
            $table->boolean('descontar_septimo_dia')->default(true);
            $table->string('metodo_calculo_dias')->default('fijos_15');
            $table->string('pagar_dia_31')->default('nadie');
            $table->boolean('redondear_neto')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_nomina');
    }
};