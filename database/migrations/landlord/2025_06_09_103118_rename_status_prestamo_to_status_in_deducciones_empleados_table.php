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
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            // Primero, se renombra la columna para evitar conflictos.
            $table->renameColumn('status_prestamo', 'status');
        });

        // En un segundo paso, se modifica la columna ya renombrada.
        // Esto asegura la compatibilidad con diferentes sistemas de bases de datos.
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            // CORRECCIÓN PRINCIPAL: Se usa ->default('Activo') con comillas simples.
            // El método ->change() aplica la modificación a la columna existente.
            $table->string('status', 50)->default('Activo')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deducciones_empleados', function (Blueprint $table) {
            // Se revierte el proceso: primero se quita el default y luego se renombra.
            $table->string('status')->change(); 
            $table->renameColumn('status', 'status_prestamo');
        });
    }
};
