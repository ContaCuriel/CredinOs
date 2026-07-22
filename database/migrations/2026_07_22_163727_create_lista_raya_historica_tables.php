<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabla del Encabezado (El periodo guardado)
        Schema::create('lista_raya_periodos', function (Blueprint $table) {
            $table->id('id_periodo_lista');
            $table->string('periodo_rango'); // Ej: '2023-10-01_2023-10-15'
            $table->unsignedBigInteger('id_sucursal')->nullable(); // null si son todas
            $table->string('status_periodo')->default('Borrador'); // 'Borrador' o 'Cerrado'
            $table->timestamps();
        });

        // Tabla de los Detalles (La Fotografía inmutable de los empleados)
        Schema::create('lista_raya_detalles', function (Blueprint $table) {
            $table->id('id_detalle_lista');
            $table->foreignId('id_periodo_lista')->constrained('lista_raya_periodos', 'id_periodo_lista')->onDelete('cascade');
            $table->unsignedBigInteger('id_empleado');
            
            // 📸 LA FOTOGRAFÍA (Datos Históricos Congelados)
            $table->decimal('sueldo_mensual_historico', 10, 2)->default(0);
            $table->decimal('sueldo_diario_historico', 10, 2)->default(0);
            $table->string('puesto_historico')->nullable();
            
            // 🕒 ASISTENCIA PROCESADA
            $table->integer('dias_periodo')->default(15);
            $table->integer('faltas_directas')->default(0);
            $table->integer('retardos_acumulados')->default(0);
            $table->integer('faltas_por_retardos')->default(0); // La regla de "3 retardos = 1 falta"
            
            // 💰 DINERO
            $table->decimal('descuento_por_faltas', 10, 2)->default(0);
            $table->decimal('otras_deducciones', 10, 2)->default(0);
            $table->decimal('percepciones_extra', 10, 2)->default(0);
            $table->decimal('total_neto', 10, 2)->default(0);
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lista_raya_detalles');
        Schema::dropIfExists('lista_raya_periodos');
    }
};