<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finiquitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_empleado')->constrained('empleados', 'id_empleado')->onDelete('cascade');
            $table->string('tipo_calculo'); // 'dias_laborados', 'finiquito', 'liquidacion'
            
            // Datos usados para el cálculo (snapshot)
            $table->date('fecha_ingreso_usada');
            $table->date('fecha_baja_usada');
            $table->decimal('salario_diario_usado', 10, 2);

            // Percepciones
            $table->decimal('monto_dias_laborados', 10, 2)->default(0);
            $table->decimal('monto_vacaciones', 10, 2)->default(0);
            $table->decimal('monto_prima_vacacional', 10, 2)->default(0);
            $table->decimal('monto_aguinaldo', 10, 2)->default(0);
            $table->decimal('monto_bono_permanencia', 10, 2)->default(0);
            $table->decimal('monto_bono_cumpleanos', 10, 2)->default(0);
            $table->decimal('monto_3_meses', 10, 2)->default(0); // Para liquidación
            $table->decimal('monto_prima_antiguedad', 10, 2)->default(0); // Para liquidación
            
            // Otras Percepciones / Deducciones
            $table->decimal('monto_caja_ahorro', 10, 2)->default(0);
            $table->decimal('deduccion_prestamo', 10, 2)->default(0);

            // Totales
            $table->decimal('total_percepciones', 12, 2)->default(0);
            $table->decimal('total_deducciones', 12, 2)->default(0);
            $table->decimal('neto_a_pagar', 12, 2)->default(0);

            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finiquitos');
    }
};
