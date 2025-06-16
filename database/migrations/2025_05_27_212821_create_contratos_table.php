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
        Schema::create('contratos', function (Blueprint $table) {
        $table->id('id_contrato'); // PK
        $table->unsignedBigInteger('id_empleado'); // FK
        $table->string('tipo_contrato', 50); // Asalariado, Honorarios
        $table->date('fecha_inicio');
        $table->date('fecha_fin');
        $table->timestamps(); // Guarda fecha_creacion y fecha_actualizacion

        // Definimos la Llave Foránea (FK)
        $table->foreign('id_empleado')->references('id_empleado')->on('empleados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
