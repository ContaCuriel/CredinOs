<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
     public function up(): void
    {
        Schema::create('asistencias', function (Blueprint $table) {
            $table->id(); // Llave primaria autoincremental (id_asistencia si prefieres, pero id es convención)

            $table->unsignedBigInteger('id_empleado');
            $table->foreign('id_empleado')
                  ->references('id_empleado')
                  ->on('empleados')
                  ->onDelete('cascade'); // Si se borra un empleado, se borran sus asistencias

            $table->date('fecha');
            $table->time('hora_llegada')->nullable();
            $table->string('status_asistencia', 20); // Ej: 'Presente', 'Falta', 'Baja_Dia', 'Incidencia'
            $table->text('notas_incidencia')->nullable();
            $table->timestamps(); // created_at y updated_at

            // Podríamos añadir un índice para búsquedas más rápidas por empleado y fecha
            $table->index(['id_empleado', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
   public function down(): void
    {
        Schema::dropIfExists('asistencias');
    }
};
