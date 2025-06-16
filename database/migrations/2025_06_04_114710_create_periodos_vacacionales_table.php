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
        Schema::create('periodos_vacacionales', function (Blueprint $table) {
            $table->id(); // Llave primaria autoincremental 'id'

            $table->unsignedBigInteger('id_empleado');
            $table->foreign('id_empleado')
                  ->references('id_empleado')
                  ->on('empleados')
                  ->onDelete('cascade'); // Si se borra un empleado, se borran sus periodos vacacionales

            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->integer('dias_tomados');
            $table->integer('ano_servicio_correspondiente')->comment('Año de servicio al que aplican estas vacaciones (1er, 2do, etc.)');
            $table->text('comentarios')->nullable();
            $table->timestamps(); // created_at y updated_at

            $table->index(['id_empleado', 'fecha_inicio']); // Para búsquedas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodos_vacacionales');
    }
};
